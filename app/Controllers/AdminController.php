<?php
/**
 * Controlador administrativo — dashboard, gestão de ocorrências e utilizadores.
 */

class AdminController extends Controller
{
    public function dashboard(): void
    {
        $this->requireAdmin();

        $userModel = new User();
        $totais = $userModel->countByEstadoOcorrencias();

        $this->renderView('admin', 'dashboard', [
            'title'  => 'Painel de Administração — SINC',
            'totais' => $totais,
        ]);
    }

    public function ocorrencias(): void
    {
        $this->requireAdmin();

        $pesquisa  = trim($_GET['pesquisa'] ?? '');
        $estado    = trim($_GET['estado'] ?? '');
        $categoria = trim($_GET['categoria'] ?? '');

        $estadosValidos = ['pendente', 'em_analise', 'resolvido', 'rejeitado'];
        if (!in_array($estado, $estadosValidos, true)) {
            $estado = '';
        }

        $categoriasValidas = ['Infraestrutura', 'Saneamento', 'Iluminação', 'Ambiente', 'Segurança', 'Transportes', 'Outro'];
        if (!in_array($categoria, $categoriasValidas, true)) {
            $categoria = '';
        }

        $ocorrenciaModel = new Ocorrencia();
        $ocorrencias = $ocorrenciaModel->getAllFiltered(
            $pesquisa !== '' ? $pesquisa : null,
            $estado !== '' ? $estado : null,
            $categoria !== '' ? $categoria : null
        );

        $sucesso = isset($_GET['atualizado']);

        $this->renderView('admin', 'ocorrencias', [
            'title'       => 'Gestão de Ocorrências — SINC',
            'ocorrencias' => $ocorrencias,
            'sucesso'     => $sucesso,
            'pesquisa'    => $pesquisa,
            'estado'      => $estado,
            'categoria'   => $categoria,
        ]);
    }

    public function detalhe(): void
    {
        $this->requireAdmin();

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            redirect(base_url('admin/ocorrencias'));
        }

        $ocorrenciaModel = new Ocorrencia();
        $ocorrencia = $ocorrenciaModel->findById($id);

        if (!$ocorrencia) {
            http_response_code(404);
            echo 'Ocorrência não encontrada.';
            return;
        }

        $historicoModel = new OcorrenciaHistorico();
        $historico = $historicoModel->getByOcorrenciaId($id);

        $this->renderView('admin', 'detalhe', [
            'title'      => 'Ocorrência #' . $id . ' — SINC',
            'ocorrencia' => $ocorrencia,
            'historico'  => $historico,
        ]);
    }

    public function alterarEstado(): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(base_url('admin/ocorrencias'));
        }

        $id     = (int) ($_POST['id'] ?? 0);
        $estado = trim($_POST['estado'] ?? '');

        $estadosValidos = ['pendente', 'em_analise', 'resolvido', 'rejeitado'];

        if ($id <= 0 || !in_array($estado, $estadosValidos, true)) {
            http_response_code(400);
            echo 'Dados inválidos.';
            exit;
        }

        $ocorrenciaModel = new Ocorrencia();
        $ocorrenciaModel->updateEstado($id, $estado, Auth::id());

        $redirectUrl = isset($_POST['origem']) && $_POST['origem'] === 'detalhe'
            ? 'admin/detalhe?id=' . $id . '&atualizado=1'
            : 'admin/ocorrencias?atualizado=1';

        redirect(base_url($redirectUrl));
    }

    public function utilizadores(): void
    {
        $this->requireAdmin();

        $userModel = new User();
        $utilizadores = $userModel->getAll();

        $sucesso = isset($_GET['utilizador']);

        $this->renderView('admin', 'utilizadores', [
            'title'        => 'Gestão de Utilizadores — SINC',
            'utilizadores' => $utilizadores,
            'sucesso'      => $sucesso,
        ]);
    }

    public function toggleUtilizador(): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(base_url('admin/utilizadores'));
        }

        $id    = (int) ($_POST['id'] ?? 0);
        $ativo = (int) ($_POST['ativo'] ?? -1);

        if ($id <= 0 || !in_array($ativo, [0, 1], true)) {
            http_response_code(400);
            echo 'Dados inválidos.';
            exit;
        }

        if ($id === Auth::id()) {
            http_response_code(400);
            echo 'Não pode desativar a sua própria conta.';
            exit;
        }

        $userModel = new User();
        $userModel->setAtivo($id, $ativo);

        redirect(base_url('admin/utilizadores?utilizador=1'));
    }
}
