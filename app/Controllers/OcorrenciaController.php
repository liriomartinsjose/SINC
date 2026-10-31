<?php

declare(strict_types=1);

/**
 * Controlador de ocorrências do SINC.
 *
 * Responsabilidades:
 * - Listar ocorrências;
 * - Consultar detalhes;
 * - Verificar permissões;
 * - Validar pedidos de alteração de estado;
 * - Delegar as operações ao modelo.
 */
final class OcorrenciaController extends Controller
{
    private Ocorrencia $ocorrencia;

    /**
     * Perfis com acesso operacional nesta primeira versão.
     *
     * A autorização por organização e município será refinada
     * quando as regras de encaminhamento estiverem implementadas.
     */
    private const PAPEIS_OPERACIONAIS = [2, 3, 4, 5];

    public function __construct()
    {
        $this->ocorrencia = new Ocorrencia();
    }

    /**
     * Lista ocorrências com pesquisa e filtros opcionais.
     */
    public function index(): void
    {
        Auth::requireAuth();

        $utilizadorId = Auth::id();
        $utilizador = Auth::user();

        if ($utilizadorId === null || !is_array($utilizador)) {
            $this->redirect('login');
        }

        $papelId = (int) ($utilizador['papel_id'] ?? 0);

        $pesquisa = trim((string) ($_GET['pesquisa'] ?? ''));
        $estado = trim((string) ($_GET['estado'] ?? ''));
        $tipo = trim((string) ($_GET['tipo'] ?? ''));

        if ($papelId === 1) {
            // O cidadão só pode consultar as próprias ocorrências.
            $ocorrencias = $this->ocorrencia->getByUserId(
                $utilizadorId
            );
        } elseif (in_array($papelId, self::PAPEIS_OPERACIONAIS, true)) {
            $ocorrencias = $this->ocorrencia->getAllFiltered(
                $pesquisa !== '' ? $pesquisa : null,
                $estado !== '' ? $estado : null,
                $tipo !== '' ? $tipo : null
            );
        } else {
            http_response_code(403);

            $this->render(
                'erros/403',
                ['mensagem' => 'Não tens permissão para consultar ocorrências.'],
                'app'
            );

            return;
        }

        $this->render(
            'ocorrencias/index',
            [
                'ocorrencias' => $ocorrencias,
                'pesquisa' => $pesquisa,
                'estadoSelecionado' => $estado,
                'tipoSelecionado' => $tipo,
                'papelId' => $papelId,
            ],
            'app'
        );
    }

    /**
     * Apresenta os detalhes de uma ocorrência.
     */
    public function show(): void
    {
        Auth::requireAuth();

        $utilizadorId = Auth::id();
        $utilizador = Auth::user();

        if ($utilizadorId === null || !is_array($utilizador)) {
            $this->redirect('login');
        }

        $papelId = (int) ($utilizador['papel_id'] ?? 0);
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if ($id === false || $id === null || $id <= 0) {
            http_response_code(400);

            $this->render(
                'erros/400',
                ['mensagem' => 'O identificador da ocorrência é inválido.'],
                'app'
            );

            return;
        }

        if ($papelId === 1) {
            $ocorrencia = $this->ocorrencia->findByIdAndUserId(
                $id,
                $utilizadorId
            );
        } elseif (in_array($papelId, self::PAPEIS_OPERACIONAIS, true)) {
            $ocorrencia = $this->ocorrencia->findById($id);
        } else {
            $ocorrencia = null;
        }

        if ($ocorrencia === null) {
            // Evita revelar a existência de ocorrências sem autorização.
            http_response_code(404);

            $this->render(
                'erros/404',
                ['mensagem' => 'A ocorrência não foi encontrada.'],
                'app'
            );

            return;
        }

        $this->render(
            'correncias/show',
            [
                'ocorrencia' => $ocorrencia,
                'papelId' => $papelId,
                'podeAlterarEstado' => in_array(
                    $papelId,
                    self::PAPEIS_OPERACIONAIS,
                    true
                ),
                'csrfToken' => Security::csrfToken(),
            ],
            'app'
        );
    }

    /**
     * Altera o estado de uma ocorrência.
     *
     * Este método deve ser chamado exclusivamente por POST.
     */
    public function updateEstado(): void
    {
        Auth::requireAuth();

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            echo 'Método não permitido.';
            return;
        }

        $utilizadorId = Auth::id();
        $utilizador = Auth::user();

        if ($utilizadorId === null || !is_array($utilizador)) {
            $this->redirect('login');
        }

        $papelId = (int) ($utilizador['papel_id'] ?? 0);

        if (!in_array($papelId, self::PAPEIS_OPERACIONAIS, true)) {
            http_response_code(403);
            echo 'Não tens permissão para alterar o estado.';
            return;
        }

        // A verificação CSRF deve rejeitar o pedido se o token for inválido.
        if (!Security::verifyCsrf($_POST['_csrf_token'] ?? null)) {
            http_response_code(403);
            echo 'O pedido expirou ou o token de segurança é inválido.';
            return;
        }

        $id = filter_var(
            $_POST['id'] ?? null,
            FILTER_VALIDATE_INT
        );

        $novoEstadoId = filter_var(
            $_POST['estado_id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (
            $id === false || $id === null || $id <= 0 ||
            $novoEstadoId === false || $novoEstadoId === null ||
            $novoEstadoId <= 0
        ) {
            http_response_code(400);
            echo 'Os dados enviados são inválidos.';
            return;
        }

        // Obter o estado actual para verificar a transição.
        $ocorrenciaAtual = $this->ocorrencia->findById($id);

        if ($ocorrenciaAtual === null) {
            http_response_code(404);
            echo 'A ocorrência não foi encontrada.';
            return;
        }

        $estadoAtualId = (int) $ocorrenciaAtual['estado_id'];

        /*
         * Transições iniciais permitidas.
         *
         * 1 Criada       -> 2 Em análise
         * 2 Em análise   -> 3 Validada ou 7 Arquivada
         * 3 Validada     -> 4 Encaminhada ou 7 Arquivada
         * 4 Encaminhada  -> 5 Em execução ou 7 Arquivada
         * 5 Em execução  -> 6 Resolvida ou 7 Arquivada
         *
         * Estas regras serão refinadas conforme as competências
         * dos moderadores, técnicos e gestores.
         */
        $transicoesPermitidas = [
            1 => [2],
            2 => [3, 7],
            3 => [4, 7],
            4 => [5, 7],
            5 => [6, 7],
        ];

        $destinos = $transicoesPermitidas[$estadoAtualId] ?? [];

        if (!in_array($novoEstadoId, $destinos, true)) {
            http_response_code(422);
            echo 'A transição de estado solicitada não é permitida.';
            return;
        }

        try {
            $sucesso = $this->ocorrencia->updateEstado(
                $id,
                $novoEstadoId,
                $utilizadorId
            );

            if (!$sucesso) {
                http_response_code(422);
                echo 'Não foi possível alterar o estado da ocorrência.';
                return;
            }

            // Redireccionamento após uma operação POST bem-sucedida.
            $this->redirect('ocorrencias/show?id=' . $id);

        } catch (Throwable $e) {
            error_log(
                '[SINC][OcorrenciaController::updateEstado] '
                . $e->getMessage()
            );

            http_response_code(500);
            echo 'Ocorreu um erro ao processar a alteração. Tenta novamente.';
        }
    }
}
