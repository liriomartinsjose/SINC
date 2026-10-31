<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Security.php';
require_once __DIR__ . '/../../core/Database.php';

class AdminDenunciaController extends Controller
{
    private PDO $db;

    private const ESTADOS = [
        'PENDENTE',
        'VALIDADA',
        'REJEITADA',
        'DUPLICADA',
    ];

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    private function exigirAdministrador(): void
    {
        Auth::requireRole(5);
    }

    private function voltar(string $mensagem, string $tipo = 'erro'): never
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['flash_admin_denuncias'] = [
                'mensagem' => $mensagem,
                'tipo' => $tipo,
            ];
        }

        $config = require __DIR__ . '/../../config/app.php';

        header(
            'Location: ' . rtrim($config['base_url'], '/')
            . '/admin/denuncias',
            true,
            303
        );
        exit;
    }

    
    public function index(): void
    {
        $this->exigirAdministrador();

        // 1. Obter e validar os filtros
        $pesquisa = trim((string) ($_GET['pesquisa'] ?? ''));
        $estado = strtoupper(trim((string) ($_GET['estado'] ?? '')));

        if (!in_array($estado, self::ESTADOS, true)) {
            $estado = '';
        }

        // 2. Consultar as denúncias
        $sql = "
            SELECT
                d.id,
                d.uuid,
                d.titulo,
                d.descricao,
                d.status,
                d.anonima,
                d.criado_em,
                t.nome AS tipo_ocorrencia,
                l.rua,
                l.numero,
                b.nome AS bairro,
                CASE
                    WHEN d.anonima = 1 THEN 'Denúncia anónima'
                    ELSE COALESCE(u.nome_completo, 'Utilizador indisponível')
                END AS nome_cidadao
            FROM denuncia d
            INNER JOIN tipo_ocorrencia t
                ON t.id = d.tipo_ocorrencia_id
            INNER JOIN localizacao l
                ON l.id = d.localizacao_id
            LEFT JOIN bairro b
                ON b.id = l.bairro_id
            LEFT JOIN utilizador u
                ON u.id = d.utilizador_id
            WHERE d.ativo = 1
            AND d.eliminado_em IS NULL
        ";

        $params = [];

        // 3. Aplicar a pesquisa
        if ($pesquisa !== '') {
            $sql .= "
                AND (
                    d.titulo LIKE :titulo
                    OR d.descricao LIKE :descricao
                    OR d.uuid LIKE :uuid
                    OR u.nome_completo LIKE :nome_cidadao
                )
            ";

            $termo = '%' . $pesquisa . '%';

            $params['titulo'] = $termo;
            $params['descricao'] = $termo;
            $params['uuid'] = $termo;
            $params['nome_cidadao'] = $termo;
        }

        // 4. Filtrar pelo estado
        if ($estado !== '') {
            $sql .= ' AND d.status = :estado';
            $params['estado'] = $estado;
        }

        $sql .= ' ORDER BY d.criado_em DESC, d.id DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $denuncias = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 5. Calcular estatísticas gerais
        $sqlEstatisticas = "
            SELECT
                COUNT(*) AS total,
                COALESCE(SUM(status = 'PENDENTE'), 0) AS pendentes,
                COALESCE(SUM(status = 'VALIDADA'), 0) AS validadas,
                COALESCE(SUM(status = 'REJEITADA'), 0) AS rejeitadas,
                COALESCE(SUM(status = 'DUPLICADA'), 0) AS duplicadas
            FROM denuncia
            WHERE ativo = 1
            AND eliminado_em IS NULL
        ";

        $stmt = $this->db->query($sqlEstatisticas);
        $estatisticas = $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total' => 0,
            'pendentes' => 0,
            'validadas' => 0,
            'rejeitadas' => 0,
            'duplicadas' => 0,
        ];

        // 6. Recuperar mensagem de feedback
        $flash = $_SESSION['flash_admin_denuncias'] ?? null;
        unset($_SESSION['flash_admin_denuncias']);

        // 7. Apresentar a página administrativa
        $this->render('admin/denuncias/index', [
            'denuncias' => $denuncias,
            'estatisticas' => $estatisticas,
            'pesquisa' => $pesquisa,
            'estado' => $estado,
            'flash' => $flash,
            'csrfToken' => Security::csrfToken(),
        ], 'admin');
    }


    
    public function alterarEstado(): void
    {
        $this->exigirAdministrador();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->voltar('Método não permitido.');
        }

        $token = (string) ($_POST['csrf_token'] ?? '');

        if (!Security::verifyCsrf($token)) {
            $this->voltar('Sessão expirada ou token de segurança inválido.');
        }

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $novoEstado = strtoupper(trim((string) ($_POST['status'] ?? '')));

        if (!$id || !in_array($novoEstado, self::ESTADOS, true)) {
            $this->voltar('Dados inválidos para atualizar a denúncia.');
        }

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                SELECT
                    d.id,
                    d.utilizador_id,
                    d.tipo_ocorrencia_id,
                    d.localizacao_id,
                    d.ocorrencia_id,
                    d.titulo,
                    d.descricao,
                    d.anonima,
                    d.status,
                    d.ativo,
                    d.eliminado_em
                FROM denuncia d
                WHERE d.id = :id
                AND d.ativo = 1
                AND d.eliminado_em IS NULL
                FOR UPDATE
            ");
            $stmt->execute(['id' => $id]);
            $denuncia = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$denuncia) {
                $this->db->rollBack();
                $this->voltar('Denúncia não encontrada ou indisponível.');
            }

            $estadoAnterior = strtoupper((string) $denuncia['status']);

            /*
            * Ao validar, a denúncia deve ficar ligada a uma ocorrência pública.
            * Não criamos outra se já existir uma associação válida.
            */
            if ($novoEstado === 'VALIDADA') {
                $ocorrenciaId = !empty($denuncia['ocorrencia_id'])
                    ? (int) $denuncia['ocorrencia_id']
                    : null;

                if ($ocorrenciaId !== null) {
                    $verificarOcorrencia = $this->db->prepare("
                        SELECT id
                        FROM ocorrencia
                        WHERE id = :id
                        AND ativo = 1
                        AND eliminado_em IS NULL
                        FOR UPDATE
                    ");
                    $verificarOcorrencia->execute(['id' => $ocorrenciaId]);

                    if (!$verificarOcorrencia->fetchColumn()) {
                        $ocorrenciaId = null;
                    }
                }

                if ($ocorrenciaId === null) {
                    /*
                    * O campo utilizador_id da ocorrência é obrigatório.
                    * Uma denúncia anónima pode continuar associada ao seu
                    * utilizador original, se existir; a anonimização pública
                    * é controlada pelo campo anonima.
                    */
                    if (empty($denuncia['utilizador_id'])) {
                        $this->db->rollBack();
                        $this->voltar(
                            'Não é possível validar esta denúncia porque não tem um utilizador associado. É necessário definir como atribuir o autor da ocorrência.'
                        );
                    }

                    $estadoStmt = $this->db->prepare("
                        SELECT id
                        FROM estado
                        WHERE id = 3
                        AND nome = 'Validada'
                        AND ativo = 1
                        AND eliminado_em IS NULL
                        LIMIT 1
                    ");
                    $estadoStmt->execute();
                    $estadoId = $estadoStmt->fetchColumn();

                    if (!$estadoId) {
                        $this->db->rollBack();
                        $this->voltar('O estado público «Validada» não está disponível.');
                    }

                    $uuid = sprintf(
                        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                        random_int(0, 0xffff),
                        random_int(0, 0xffff),
                        random_int(0, 0xffff),
                        random_int(0, 0x0fff) | 0x4000,
                        random_int(0, 0x3fff) | 0x8000,
                        random_int(0, 0xffff),
                        random_int(0, 0xffff),
                        random_int(0, 0xffff)
                    );

                    $criarOcorrencia = $this->db->prepare("
                        INSERT INTO ocorrencia (
                            uuid,
                            utilizador_id,
                            tipo_ocorrencia_id,
                            localizacao_id,
                            estado_id,
                            titulo,
                            descricao,
                            anonima,
                            data_validacao,
                            ativo,
                            versao,
                            criado_em,
                            atualizado_em
                        ) VALUES (
                            :uuid,
                            :utilizador_id,
                            :tipo_ocorrencia_id,
                            :localizacao_id,
                            :estado_id,
                            :titulo,
                            :descricao,
                            :anonima,
                            CURRENT_TIMESTAMP,
                            1,
                            1,
                            CURRENT_TIMESTAMP,
                            CURRENT_TIMESTAMP
                        )
                    ");

                    $criarOcorrencia->execute([
                        'uuid' => $uuid,
                        'utilizador_id' => (int) $denuncia['utilizador_id'],
                        'tipo_ocorrencia_id' => (int) $denuncia['tipo_ocorrencia_id'],
                        'localizacao_id' => (int) $denuncia['localizacao_id'],
                        'estado_id' => (int) $estadoId,
                        'titulo' => $denuncia['titulo'],
                        'descricao' => $denuncia['descricao'],
                        'anonima' => (int) $denuncia['anonima'],
                    ]);

                    $ocorrenciaId = (int) $this->db->lastInsertId();
                }

                $ligarOcorrencia = $this->db->prepare("
                    UPDATE denuncia
                    SET ocorrencia_id = :ocorrencia_id,
                        status = 'VALIDADA',
                        duplicada = 0,
                        versao = versao + 1,
                        atualizado_em = CURRENT_TIMESTAMP
                    WHERE id = :id
                ");
                $ligarOcorrencia->execute([
                    'ocorrencia_id' => $ocorrenciaId,
                    'id' => $id,
                ]);
            } else {
                $atualizar = $this->db->prepare("
                    UPDATE denuncia
                    SET status = :status,
                        duplicada = :duplicada,
                        versao = versao + 1,
                        atualizado_em = CURRENT_TIMESTAMP
                    WHERE id = :id
                ");

                $atualizar->execute([
                    'status' => $novoEstado,
                    'duplicada' => $novoEstado === 'DUPLICADA' ? 1 : 0,
                    'id' => $id,
                ]);
            }

            $this->db->commit();

            $mensagem = match ($novoEstado) {
                'VALIDADA' => 'Denúncia validada e associada a uma ocorrência pública.',
                'REJEITADA' => 'Denúncia rejeitada.',
                'DUPLICADA' => 'Denúncia marcada como duplicada.',
                default => 'Denúncia devolvida ao estado pendente.',
            };

            $this->voltar($mensagem, 'sucesso');

        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            error_log(
                '[AdminDenunciaController::alterarEstado] ' .
                $e->getMessage()
            );

            $this->voltar(
                'Não foi possível atualizar a denúncia. Verifica os registos de erro do PHP.'
            );
        }
    }

}
