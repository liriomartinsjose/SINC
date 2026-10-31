<?php
declare(strict_types=1);

final class OrganizacaoController extends Controller
{
    private Organizacao $organizacao;

    public function __construct()
    {
        $this->organizacao = new Organizacao();
    }

    private function autorizar(): array
    {
        return Auth::requireRole(5);
    }

    private function validarCsrf(): void
    {
        $token = $_POST['_csrf_token'] ?? '';
        $esperado = Security::csrfToken();

        if (
            !is_string($token) ||
            !is_string($esperado) ||
            $token === '' ||
            !hash_equals($esperado, $token)
        ) {
            http_response_code(403);
            exit('Pedido recusado. Atualize a página e tente novamente.');
        }
    }

    private function voltar(string $mensagem): void
    {
        $this->redirect(
            '/admin/organizacoes?mensagem=' . rawurlencode($mensagem)
        );
    }

    public function index(): void
    {
        $user = $this->autorizar();

        $pesquisa = trim((string) ($_GET['pesquisa'] ?? ''));

        $this->render('admin/organizacoes/index', [
            'user' => $user,
            'pageTitle' => 'Organizações',
            'pageStyles' => [
                '/public/assets/css/admin/organizacoes.css',
            ],
            'organizacoes' => $this->organizacao->listar($pesquisa),
            'pesquisa' => $pesquisa,
            'mensagem' => trim((string) ($_GET['mensagem'] ?? '')),
        ], 'admin');
    }

    public function criar(): void
    {
        $user = $this->autorizar();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validarCsrf();

            try {
                $this->organizacao->criar($_POST);
                $this->voltar('criada');
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') {
                    $erro = 'Já existe uma organização com esse nome ou código oficial.';
                } else {
                    error_log('Erro ao criar organização: ' . $e->getMessage());
                    $erro = 'Não foi possível guardar a organização.';
                }
            } catch (InvalidArgumentException $e) {
                $erro = $e->getMessage();
            } catch (Throwable $e) {
                error_log('Erro ao criar organização: ' . $e->getMessage());
                $erro = 'Ocorreu um erro inesperado ao guardar a organização.';
            }
        }

        $this->render('admin/organizacoes/form', [
            'user' => $user,
            'pageTitle' => 'Nova organização',
            'pageStyles' => [
                '/public/assets/css/admin/organizacoes.css',
            ],
            'modo' => 'criar',
            'organizacao' => [],
            'provincias' => $this->organizacao->listarProvincias(),
            'municipios' => $this->organizacao->listarMunicipios(),
            'erro' => $erro ?? '',
        ], 'admin');
    }

    public function editar(): void
    {
        $user = $this->autorizar();

        $id = filter_var(
            $_GET['id'] ?? $_POST['id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($id === false || $id === null) {
            $this->redirect('/admin/organizacoes');
            return;
        }

        $organizacao = $this->organizacao->encontrarPorId((int) $id);

        if ($organizacao === null) {
            http_response_code(404);
            $this->render('errors/404', [
                'user' => $user,
                'pageTitle' => 'Organização não encontrada',
            ], 'admin');
            return;
        }

        $erro = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validarCsrf();

            try {
                $this->organizacao->actualizar((int) $id, $_POST);
                $this->voltar('atualizada');
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') {
                    $erro = 'Já existe uma organização com esse nome ou código oficial.';
                } else {
                    error_log('Erro ao editar organização: ' . $e->getMessage());
                    $erro = 'Não foi possível atualizar a organização.';
                }
            } catch (InvalidArgumentException $e) {
                $erro = $e->getMessage();
            } catch (Throwable $e) {
                error_log('Erro ao editar organização: ' . $e->getMessage());
                $erro = 'Ocorreu um erro inesperado ao atualizar a organização.';
            }

            $organizacao = array_merge($organizacao, $_POST);
        }

        $provinciaId = isset($organizacao['provincia_id'])
            ? (int) $organizacao['provincia_id']
            : null;

        $this->render('admin/organizacoes/form', [
            'user' => $user,
            'pageTitle' => 'Editar organização',
            'pageStyles' => [
                '/public/assets/css/admin/organizacoes.css',
            ],
            'modo' => 'editar',
            'organizacao' => $organizacao,
            'provincias' => $this->organizacao->listarProvincias(),
            'municipios' => $this->organizacao->listarMunicipios(
                $provinciaId > 0 ? $provinciaId : null
            ),
            'erro' => $erro,
        ], 'admin');
    }

    public function alternarEstado(): void
    {
        $this->autorizar();
        $this->validarCsrf();

        $id = filter_var(
            $_POST['id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        $estado = $_POST['ativo'] ?? null;

        if (
            $id === false ||
            $id === null ||
            !in_array((string) $estado, ['0', '1'], true)
        ) {
            $this->voltar('pedido_invalido');
            return;
        }

        try {
            $this->organizacao->definirEstado(
                (int) $id,
                (string) $estado === '1'
            );

            $this->voltar('estado_atualizado');
        } catch (Throwable $e) {
            error_log('Erro ao alterar estado da organização: ' . $e->getMessage());
            $this->voltar('erro');
        }
    }
}
