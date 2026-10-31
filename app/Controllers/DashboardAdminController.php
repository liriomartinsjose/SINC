<?php

declare(strict_types=1);

final class DashboardAdminController extends Controller
{
    private const ESTADOS = [
        1 => 'Criada',
        2 => 'Em análise',
        3 => 'Validada',
        4 => 'Encaminhada',
        5 => 'Em execução',
        6 => 'Resolvida',
        7 => 'Arquivada',
    ];

    public function index(): void
    {
        $user = Auth::requireRole(5);
        $pdo = Database::getConnection();

        $filtros = [
            'pesquisa' => trim((string) ($_GET['pesquisa'] ?? '')),
            'estado' => (int) ($_GET['estado'] ?? 0),
            'gravidade' => strtoupper(trim((string) ($_GET['gravidade'] ?? ''))),
            'periodo' => (string) ($_GET['periodo'] ?? '30'),
        ];

        if (!in_array($filtros['estado'], [0, 1, 2, 3, 4, 5, 6, 7], true)) {
            $filtros['estado'] = 0;
        }

        if (!in_array($filtros['gravidade'], ['', 'BAIXA', 'MEDIA', 'ALTA', 'CRITICA'], true)) {
            $filtros['gravidade'] = '';
        }

        if (!in_array($filtros['periodo'], ['7', '30', '90', '365', 'todos'], true)) {
            $filtros['periodo'] = '30';
        }

        $estatisticas = [
            'total' => 0,
            'pendentes' => 0,
            'execucao' => 0,
            'resolvidas' => 0,
            'criticas' => 0,
            'taxa_resolucao' => 0,
        ];

        $porEstado = [];
        $porGravidade = [];
        $evolucao = [];
        $denuncias = [];
        $atividade = [];
        $totalFiltrado = 0;
        $erroConsulta = false;

        foreach (self::ESTADOS as $id => $nome) {
            $porEstado[$id] = [
                'id' => $id,
                'nome' => $nome,
                'total' => 0,
            ];
        }

        foreach (['BAIXA', 'MEDIA', 'ALTA', 'CRITICA'] as $gravidade) {
            $porGravidade[$gravidade] = [
                'nome' => $gravidade,
                'total' => 0,
            ];
        }

        try {
            $sqlEstatisticas = "
                SELECT
                    COUNT(*) AS total,
                    COALESCE(SUM(estado_id IN (1, 2)), 0) AS pendentes,
                    COALESCE(SUM(estado_id = 5), 0) AS execucao,
                    COALESCE(SUM(estado_id = 6), 0) AS resolvidas,
                    COALESCE(SUM(gravidade = 'CRITICA'), 0) AS criticas
                FROM ocorrencia
                WHERE eliminado_em IS NULL
                  AND ativo = 1
            ";

            $estatisticas = $pdo->query($sqlEstatisticas)->fetch(PDO::FETCH_ASSOC) ?: $estatisticas;

            $total = (int) $estatisticas['total'];
            $resolvidas = (int) $estatisticas['resolvidas'];

            $estatisticas['taxa_resolucao'] = $total > 0
                ? round(($resolvidas / $total) * 100, 1)
                : 0;

            $stmtEstados = $pdo->query("
                SELECT estado_id, COUNT(*) AS total
                FROM ocorrencia
                WHERE eliminado_em IS NULL
                  AND ativo = 1
                GROUP BY estado_id
            ");

            foreach ($stmtEstados->fetchAll(PDO::FETCH_ASSOC) as $linha) {
                $id = (int) $linha['estado_id'];

                if (isset($porEstado[$id])) {
                    $porEstado[$id]['total'] = (int) $linha['total'];
                }
            }

            $stmtGravidade = $pdo->query("
                SELECT gravidade, COUNT(*) AS total
                FROM ocorrencia
                WHERE eliminado_em IS NULL
                  AND ativo = 1
                GROUP BY gravidade
            ");

            foreach ($stmtGravidade->fetchAll(PDO::FETCH_ASSOC) as $linha) {
                $gravidade = (string) $linha['gravidade'];

                if (isset($porGravidade[$gravidade])) {
                    $porGravidade[$gravidade]['total'] = (int) $linha['total'];
                }
            }

            $stmtEvolucao = $pdo->query("
                SELECT DATE(criado_em) AS dia, COUNT(*) AS total
                FROM ocorrencia
                WHERE eliminado_em IS NULL
                  AND ativo = 1
                  AND criado_em >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                GROUP BY DATE(criado_em)
                ORDER BY dia ASC
            ");

            $contagensDia = [];

            foreach ($stmtEvolucao->fetchAll(PDO::FETCH_ASSOC) as $linha) {
                $contagensDia[(string) $linha['dia']] = (int) $linha['total'];
            }

            for ($i = 6; $i >= 0; $i--) {
                $dia = date('Y-m-d', strtotime("-{$i} days"));

                $evolucao[] = [
                    'dia' => $dia,
                    'rotulo' => date('d/m', strtotime($dia)),
                    'total' => $contagensDia[$dia] ?? 0,
                ];
            }

            $condicoes = [
                'o.eliminado_em IS NULL',
                'o.ativo = 1',
            ];

            $parametros = [];

            if ($filtros['pesquisa'] !== '') {
                $condicoes[] = '(o.titulo LIKE :pesquisa OR o.uuid LIKE :referencia)';
                $termo = '%' . $filtros['pesquisa'] . '%';
                $parametros['pesquisa'] = $termo;
                $parametros['referencia'] = $termo;
            }

            if ($filtros['estado'] > 0) {
                $condicoes[] = 'o.estado_id = :estado';
                $parametros['estado'] = $filtros['estado'];
            }

            if ($filtros['gravidade'] !== '') {
                $condicoes[] = 'o.gravidade = :gravidade';
                $parametros['gravidade'] = $filtros['gravidade'];
            }

            if ($filtros['periodo'] !== 'todos') {
                $dias = (int) $filtros['periodo'];
                $condicoes[] = "o.criado_em >= DATE_SUB(CURDATE(), INTERVAL {$dias} DAY)";
            }

            $where = implode(' AND ', $condicoes);

            $stmtContagem = $pdo->prepare("
                SELECT COUNT(*)
                FROM ocorrencia o
                WHERE {$where}
            ");
            $stmtContagem->execute($parametros);
            $totalFiltrado = (int) $stmtContagem->fetchColumn();

            $stmtDenuncias = $pdo->prepare("
                SELECT
                    o.id,
                    o.uuid,
                    o.titulo,
                    o.gravidade,
                    o.prioridade,
                    o.estado_id,
                    o.tipo_ocorrencia_id,
                    o.localizacao_id,
                    o.criado_em
                FROM ocorrencia o
                WHERE {$where}
                ORDER BY
                    CASE o.gravidade
                        WHEN 'CRITICA' THEN 1
                        WHEN 'ALTA' THEN 2
                        WHEN 'MEDIA' THEN 3
                        ELSE 4
                    END,
                    o.criado_em DESC
                LIMIT 10
            ");

            $stmtDenuncias->execute($parametros);
            $denuncias = $stmtDenuncias->fetchAll(PDO::FETCH_ASSOC);

            // O histórico é opcional: a falha desta consulta não bloqueia o dashboard.
            try {
                $stmtAtividade = $pdo->query("
                    SELECT acao, descricao, data_evento
                    FROM historico
                    ORDER BY data_evento DESC
                    LIMIT 5
                ");

                $atividade = $stmtAtividade->fetchAll(PDO::FETCH_ASSOC);
            } catch (Throwable $e) {
                error_log('SINC dashboard: histórico indisponível — ' . $e->getMessage());
            }
        } catch (Throwable $e) {
            error_log('SINC dashboard administrativo: ' . $e->getMessage());
            $erroConsulta = true;
        }

        $this->render(
            'admin/dashboard',
            [
                'user' => $user,
                'pageTitle' => 'Painel de controlo',
                'pageStyles' => [
                    '/public/assets/css/admin/dashboard.css',
                ],
                'pageScripts' => [
                    '/public/assets/js/admin/dashboard.js',
                ],
                'estatisticas' => $estatisticas,
                'porEstado' => array_values($porEstado),
                'porGravidade' => array_values($porGravidade),
                'evolucao' => $evolucao,
                'denuncias' => $denuncias,
                'atividade' => $atividade,
                'filtros' => $filtros,
                'totalFiltrado' => $totalFiltrado,
                'erroConsulta' => $erroConsulta,
            ],
            'admin'
        );
    }
}
