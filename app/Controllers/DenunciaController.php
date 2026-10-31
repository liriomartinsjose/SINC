<?php

declare(strict_types=1);

require_once __DIR__ . '/../Models/Denuncia.php';
require_once __DIR__ . '/../../core/Security.php';

final class DenunciaController extends Controller
{
    private Denuncia $model;

    private const MAX_IMAGENS = 5;
    private const MAX_BYTES_IMAGEM = 5242880;

    private const TIPOS_IMAGEM = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    public function __construct()
    {
        $this->model = new Denuncia();
    }

    private function utilizadorId(): int
    {
        $user = Auth::requireRole(1);

        $id = (int) ($user['id'] ?? 0);

        if ($id <= 0) {
            throw new RuntimeException(
                'Não foi possível identificar o utilizador autenticado.'
            );
        }

        return $id;
    }

    private function baseUrl(): string
    {
        $config = require __DIR__ . '/../../config/app.php';

        return rtrim($config['base_url'], '/');
    }

    private function voltarComErro(
        string $mensagem,
        array $dados = []
    ): void {
        http_response_code(422);

        $this->render('denuncias/criar', array_merge([
            'tipos' => $this->model->getTiposAtivos(),
            'bairros' => $this->model->getBairrosAtivos(),
            'erros' => [$mensagem],
            'dados' => $dados,
        ], $dados), 'app');

        return;
    }

    public function index(): void
    {
        $utilizadorId = $this->utilizadorId();

        $denuncias = $this->model->getByUserId($utilizadorId);

        $this->render('cidadao/denuncias/minhas_denuncias', [
            'denuncias' => $denuncias,
            'user' => Auth::user(),
        ], 'app');
    }

    public function criar(): void
    {
        $utilizadorId = $this->utilizadorId();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->render('cidadao/denuncias/criar', [
                'tipos' => $this->model->getTiposAtivos(),
                'bairros' => $this->model->getBairrosAtivos(),
                'erros' => [],
                'dados' => [],
            ], 'app');

            return;
        }

        $dados = [
            'titulo' => trim((string) ($_POST['titulo'] ?? '')),
            'descricao' => trim((string) ($_POST['descricao'] ?? '')),
            'tipo_ocorrencia_id' => (int) ($_POST['tipo_ocorrencia_id'] ?? 0),
            'bairro_id' => (int) ($_POST['bairro_id'] ?? 0),
            'rua' => trim((string) ($_POST['rua'] ?? '')),
            'numero' => trim((string) ($_POST['numero'] ?? '')),
            'referencia' => trim((string) ($_POST['referencia'] ?? '')),
            'latitude' => trim((string) ($_POST['latitude'] ?? '')),
            'longitude' => trim((string) ($_POST['longitude'] ?? '')),
            'anonima' => isset($_POST['anonima']) ? 1 : 0,
        ];

        if (!Security::verifyCsrf($_POST['_csrf_token'] ?? null)) {
            $this->voltarComErro(
                'A sessão do formulário expirou. Actualiza a página e tenta novamente.',
                $dados
            );
            return;
        }

        $erros = [];

        if ($dados['titulo'] === '' || mb_strlen($dados['titulo']) > 200) {
            $erros[] = 'O título é obrigatório e deve ter até 200 caracteres.';
        }

        if ($dados['descricao'] === '') {
            $erros[] = 'Descreve o problema observado.';
        } elseif (mb_strlen($dados['descricao']) > 20000) {
            $erros[] = 'A descrição não pode ultrapassar 20 000 caracteres.';
        }

        if (
            $dados['tipo_ocorrencia_id'] <= 0
            || !$this->model->tipoExiste($dados['tipo_ocorrencia_id'])
        ) {
            $erros[] = 'Selecciona um tipo de ocorrência válido.';
        }

        if (
            $dados['bairro_id'] <= 0
            || !$this->model->bairroExiste($dados['bairro_id'])
        ) {
            $erros[] = 'Selecciona um bairro válido.';
        }

        $latitude = filter_var($dados['latitude'], FILTER_VALIDATE_FLOAT);
        $longitude = filter_var($dados['longitude'], FILTER_VALIDATE_FLOAT);

        if (
            $latitude === false
            || $longitude === false
            || $latitude < -90
            || $latitude > 90
            || $longitude < -180
            || $longitude > 180
        ) {
            $erros[] = 'Indica a localização no mapa ou permite obter a posição GPS.';
        } else {
            $dados['latitude'] = (float) $latitude;
            $dados['longitude'] = (float) $longitude;
        }

        if ($erros !== []) {
            $this->voltarComErro(implode(' ', $erros), $dados);
            return;
        }

        $ficheirosCriados = [];
        $anexos = [];

        try {
            $anexos = $this->prepararImagens($ficheirosCriados);

            $denunciaId = $this->model->criar(
                $dados,
                $utilizadorId,
                $anexos
            );

            $this->redirect(
                '/denuncias/detalhes?id=' . $denunciaId
            );
        } catch (Throwable $e) {
            foreach ($ficheirosCriados as $ficheiro) {
                if (is_file($ficheiro)) {
                    @unlink($ficheiro);
                }
            }

            error_log('[SINC] Erro ao criar denúncia: ' . $e->getMessage());

            $this->voltarComErro(
                'Não foi possível registar a denúncia. Verifica os dados e as imagens e tenta novamente.',
                $dados
            );
        }
    }

    /**
     * Valida e guarda imagens fora da pasta pública.
     *
     * $ficheirosCriados é preenchido para permitir limpeza em caso de erro.
     */
    private function prepararImagens(array &$ficheirosCriados): array
    {
        if (!isset($_FILES['imagens'])) {
            return [];
        }

        $ficheiros = $_FILES['imagens'];

        if (!is_array($ficheiros['name'] ?? null)) {
            throw new RuntimeException('O formato dos ficheiros é inválido.');
        }

        $total = count($ficheiros['name']);

        if ($total > self::MAX_IMAGENS) {
            throw new RuntimeException('Podes anexar no máximo cinco imagens.');
        }

        $anexos = [];

        if ($total === 0) {
            return [];
        }

        $directorio = dirname(__DIR__, 2) . '/storage/denuncias';

        if (!is_dir($directorio) && !mkdir($directorio, 0750, true)) {
            throw new RuntimeException('Não foi possível preparar o armazenamento.');
        }

        if (!is_writable($directorio)) {
            throw new RuntimeException('A pasta de imagens não permite gravação.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);

        for ($i = 0; $i < $total; $i++) {
            $erroUpload = (int) ($ficheiros['error'][$i] ?? UPLOAD_ERR_NO_FILE);

            if ($erroUpload === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            if ($erroUpload !== UPLOAD_ERR_OK) {
                throw new RuntimeException(
                    'Uma das imagens não foi carregada correctamente.'
                );
            }

            $temporario = $ficheiros['tmp_name'][$i] ?? '';
            $nomeOriginal = basename(
                (string) ($ficheiros['name'][$i] ?? 'imagem')
            );
            $tamanho = (int) ($ficheiros['size'][$i] ?? 0);

            if (
                !is_uploaded_file($temporario)
                || $tamanho <= 0
                || $tamanho > self::MAX_BYTES_IMAGEM
            ) {
                throw new RuntimeException(
                    'Cada imagem deve ter um tamanho máximo de 5 MB.'
                );
            }

            $mime = $finfo->file($temporario);

            if (!is_string($mime) || !isset(self::TIPOS_IMAGEM[$mime])) {
                throw new RuntimeException(
                    'Só são permitidas imagens JPEG, PNG ou WebP.'
                );
            }

            $infoImagem = @getimagesize($temporario);

            if (
                $infoImagem === false
                || ($infoImagem['mime'] ?? '') !== $mime
            ) {
                throw new RuntimeException(
                    'Um dos ficheiros não é uma imagem válida.'
                );
            }

            $extensao = self::TIPOS_IMAGEM[$mime];
            $nomeSeguro = bin2hex(random_bytes(24)) . '.' . $extensao;
            $destino = $directorio . '/' . $nomeSeguro;

            if (!move_uploaded_file($temporario, $destino)) {
                throw new RuntimeException(
                    'Não foi possível guardar uma das imagens.'
                );
            }

            $ficheirosCriados[] = $destino;

            $anexos[] = [
                'uuid' => $this->gerarUuidPublico(),
                'nome_original' => mb_substr($nomeOriginal, 0, 255),
                'nome_arquivo' => $nomeSeguro,
                'tipo_mime' => $mime,
                'extensao' => $extensao,
                'tamanho_bytes' => filesize($destino),
                'caminho' => 'storage/denuncias/' . $nomeSeguro,
                'hash_sha256' => hash_file('sha256', $destino),
                'descricao' => null,
                'ficheiro_absoluto' => $destino,
            ];
        }

        return $anexos;
    }

    private function gerarUuidPublico(): string
    {
        $bytes = random_bytes(16);

        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

        $hex = bin2hex($bytes);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12)
        );
    }

    public function detalhes(): void
    {
        $utilizadorId = $this->utilizadorId();
        $denunciaId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$denunciaId || $denunciaId <= 0) {
            http_response_code(404);
            $this->render('errors/404', [], 'app');
            return;
        }

        $denuncia = $this->model->findByIdAndUserId(
            (int) $denunciaId,
            $utilizadorId
        );

        if ($denuncia === null) {
            http_response_code(404);
            $this->render('errors/404', [], 'app');
            return;
        }

        $anexos = $this->model->getAnexosByDenunciaAndUser(
            (int) $denunciaId,
            $utilizadorId
        );

        $this->render('cidadao/denuncias/detalhes', [
            'denuncia' => $denuncia,
            'anexos' => $anexos,
            'user' => Auth::user(),
        ], 'app');
    }

    /**
     * Entrega uma imagem apenas ao proprietário autenticado da denúncia.
     */
    public function imagem(): void
    {
        $utilizadorId = $this->utilizadorId();
        $anexoId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$anexoId || $anexoId <= 0) {
            http_response_code(404);
            exit;
        }

        $anexo = $this->model->findAnexoByIdAndUser(
            (int) $anexoId,
            $utilizadorId
        );

        if ($anexo === null) {
            http_response_code(404);
            exit;
        }

        $nome = (string) $anexo['nome_arquivo'];

        // Aceitar apenas nomes aleatórios com extensões de imagem permitidas.
        if (!preg_match('/\A[a-f0-9]{48}\.(jpg|png|webp)\z/', $nome)) {
            http_response_code(404);
            exit;
        }

        $directorio = realpath(dirname(__DIR__, 2) . '/storage/denuncias');
        $caminho = realpath(
            dirname(__DIR__, 2) . '/storage/denuncias/' . $nome
        );

        if (
            $directorio === false
            || $caminho === false
            || !str_starts_with($caminho, $directorio . DIRECTORY_SEPARATOR)
            || !is_file($caminho)
        ) {
            http_response_code(404);
            exit;
        }

        $tiposPermitidos = [
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
        ];

        $mime = $tiposPermitidos[$anexo['extensao'] ?? ''] ?? null;

        if ($mime === null || $mime !== ($anexo['tipo_mime'] ?? null)) {
            http_response_code(404);
            exit;
        }

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . (string) filesize($caminho));
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store');
        header('Content-Disposition: inline; filename="imagem.' .
            $anexo['extensao'] . '"');

        readfile($caminho);
        exit;
    }
}
