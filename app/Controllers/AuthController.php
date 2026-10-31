<?php

final class AuthController extends Controller
{
    private Utilizador $utilizadores;

    public function __construct()
    {
        $this->utilizadores = new Utilizador();
    }

    public function mostrarRegisto(): void
    {
        Auth::requireGuest();

        $this->render('auth/registo', [
            'errors' => [],
            'old' => [],
        ]);
    }

    public function registar(): void
    {
        Auth::requireGuest();

        if (!Security::verifyCsrf($_POST['_csrf_token'] ?? null)) {
            http_response_code(419);

            $this->render('auth/registo', [
                'errors' => [
                    'formulario' =>
                        'A sessão do formulário expirou. Atualiza a página e tenta novamente.',
                ],
                'old' => [],
            ]);
            return;
        }

        $name = trim((string) ($_POST['nome_completo'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $phone = trim((string) ($_POST['telefone'] ?? ''));
        $password = (string) ($_POST['senha'] ?? '');
        $confirmation = (string) ($_POST['confirmar_senha'] ?? '');

        $old = [
            'nome_completo' => $name,
            'email' => $email,
            'telefone' => $phone,
        ];

        $errors = [];

        if (mb_strlen($name) < 3 || mb_strlen($name) > 150) {
            $errors['nome_completo'] =
                'O nome deve ter entre 3 e 150 caracteres.';
        }

        if (
            strlen($email) > 150 ||
            !filter_var($email, FILTER_VALIDATE_EMAIL)
        ) {
            $errors['email'] = 'Introduz um endereço de email válido.';
        }

        if ($phone !== '' && mb_strlen($phone) > 30) {
            $errors['telefone'] =
                'O telefone não pode ultrapassar 30 caracteres.';
        }

        if (strlen($password) < 12) {
            $errors['senha'] =
                'A palavra-passe deve ter pelo menos 12 caracteres.';
        }

        if ($password !== $confirmation) {
            $errors['confirmar_senha'] =
                'As palavras-passe não coincidem.';
        }

        if ($errors !== []) {
            $this->render('auth/registo', [
                'errors' => $errors,
                'old' => $old,
            ]);
            return;
        }

        try {
            $id = $this->utilizadores->createCitizen(
                $name,
                $email,
                $phone,
                $password
            );

            // Não autenticar automaticamente antes de verificar a conta.
            // O utilizador seguirá para o formulário de login.
            header(
                'Location: /sinc/login?registado=1',
                true,
                303
            );
            exit;
        } catch (PDOException $exception) {
            error_log('[SINC] Erro de registo: ' . $exception->getMessage());

            if ($exception->getCode() === '23000') {
                $this->render('auth/registo', [
                    'errors' => [
                        'formulario' =>
                            'O email ou telefone indicado já está registado.',
                    ],
                    'old' => $old,
                ]);
                return;
            }

            http_response_code(500);

            $this->render('auth/registo', [
                'errors' => [
                    'formulario' =>
                        'Não foi possível concluir o registo. Tenta novamente.',
                ],
                'old' => $old,
            ]);
        } catch (Throwable $exception) {
            error_log('[SINC] Falha no registo: ' . $exception->getMessage());

            http_response_code(500);

            $this->render('auth/registo', [
                'errors' => [
                    'formulario' =>
                        'Ocorreu um erro ao criar a conta.',
                ],
                'old' => $old,
            ]);
        }
    }

    public function mostrarLogin(): void
    {
        Auth::requireGuest();

        $this->render('auth/login', [
            'errors' => [],
            'email' => '',
            'registado' => isset($_GET['registado']),
            'terminou' => isset($_GET['terminou']),
        ]);
    }

    public function entrar(): void
    {
        Auth::requireGuest();

        if (!Security::verifyCsrf($_POST['_csrf_token'] ?? null)) {
            http_response_code(419);

            $this->render('auth/login', [
                'errors' => [
                    'formulario' =>
                        'A sessão expirou. Atualiza a página e tenta novamente.',
                ],
                'email' => '',
                'registado' => false,
                'terminou' => false,
            ]);
            return;
        }

        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['senha'] ?? '');

        $errors = [];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            $errors['formulario'] =
                'O email ou a palavra-passe estão incorretos.';
        } else {
            $user = $this->utilizadores->findActiveByEmail($email);

            if (
                $user === null ||
                !password_verify($password, $user['senha_hash'])
            ) {
                $errors['formulario'] =
                    'O email ou a palavra-passe estão incorretos.';
            } else {
                Auth::login($user);
                $this->utilizadores->updateLastLogin((int) $user['id']);

                $destino = $this->obterDestinoPorPapel($user);

if ($destino === null) {
    Auth::logout();

    error_log(
        '[SINC] Login recusado: papel sem destino configurado.'
    );

    header('Location: /sinc/login?erro=papel', true, 303);
    exit;
}

$config = require __DIR__ . '/../../config/app.php';

header(
    'Location: ' . rtrim($config['base_url'], '/') . $destino,
    true,
    303
);
exit;
            }
        }

        http_response_code(422);

        $this->render('auth/login', [
            'errors' => $errors,
            'email' => $email,
            'registado' => false,
            'terminou' => false,
        ]);
    }


    private function obterDestinoPorPapel(array $user): ?string
{
    $destinos = [
        1 => '/cidadao/dashboard',
        2 => '/operacional/moderador/dashboard',
        3 => '/operacional/tecnico/dashboard',
        4 => '/operacional/gestor-municipal/dashboard',
        5 => '/admin/dashboard',
    ];

    $papelId = (int) ($user['papel_id'] ?? 0);

    return $destinos[$papelId] ?? null;
}

    public function sair(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            echo 'Método não permitido.';
            return;
        }

        if (!Security::verifyCsrf($_POST['_csrf_token'] ?? null)) {
            http_response_code(419);
            echo 'A sessão expirou. Atualiza a página antes de sair.';
            return;
        }

        Auth::logout();

        $config = require __DIR__ . '/../../config/app.php';

        header(
            'Location: ' . rtrim($config['base_url'], '/') . '/login?terminou=1',
            true,
            303
        );
        exit;
    }
}