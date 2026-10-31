
<?php
$pageTitle = 'Iniciar sessão';
$errors = $errors ?? [];
$email = $email ?? '';
?>

<div class="auth-page">
    <main class="auth-shell">

        <aside class="auth-showcase">
            <a class="auth-brand" href="<?= Security::escape($baseUrl) ?>/">
                <span class="auth-brand-mark">S</span>
                <span>SINC<span class="auth-brand-dot">.</span></span>
            </a>

            <div class="showcase-content">
                <span class="showcase-label">
                    <span class="status-dot"></span>
                    PARTICIPAÇÃO CÍVICA DIGITAL
                </span>

                <h2>Uma comunidade melhor começa com <span>a tua voz.</span></h2>

                <p>
                    Comunica problemas da tua comunidade, acompanha as ocorrências
                    e participa na construção de soluções.
                </p>

                <div class="showcase-visual" aria-hidden="true">
                    <div class="visual-orbit orbit-one"></div>
                    <div class="visual-orbit orbit-two"></div>
                    <div class="visual-orbit orbit-three"></div>

                    <div class="visual-center">
                        <span class="visual-center-symbol">S</span>
                    </div>

                    <div class="visual-node node-one">
                        <span class="node-icon">01</span>
                    </div>
                    <div class="visual-node node-two">
                        <span class="node-icon">02</span>
                    </div>
                    <div class="visual-node node-three">
                        <span class="node-icon">03</span>
                    </div>
                </div>

                <div class="showcase-note">
                    <span class="note-check">✓</span>
                    <div>
                        <strong>A tua participação importa.</strong>
                        <span>Pequenas ações podem gerar grandes mudanças.</span>
                    </div>
                </div>
            </div>

            <div class="showcase-footer">
                <span>Transparência. Participação. Mudança.</span>
                <span class="showcase-footer-line"></span>
            </div>
        </aside>

        <section class="auth-panel">
            <div class="auth-panel-inner">

                <div class="auth-mobile-brand">
                    <span class="auth-brand-mark">S</span>
                    <span>SINC<span class="auth-brand-dot">.</span></span>
                </div>

                <div class="auth-heading">
                    <span class="eyebrow">BEM-VINDO DE VOLTA</span>
                    <h1>Que bom ter-te <span>de volta.</span></h1>
                    <p>Inicia sessão para continuares a participar.</p>
                </div>

                <?php if (!empty($registado)): ?>
                    <div class="alert alert-success" role="status">
                        <span class="alert-icon">✓</span>
                        <span>Conta criada com sucesso. Já podes iniciar sessão.</span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($terminou)): ?>
                    <div class="alert alert-success" role="status">
                        <span class="alert-icon">✓</span>
                        <span>A tua sessão foi terminada com sucesso.</span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors['formulario'])): ?>
                    <div class="alert alert-error" role="alert">
                        <span class="alert-icon">!</span>
                        <span><?= Security::escape($errors['formulario']) ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST"
                      action="<?= Security::escape($baseUrl) ?>/login"
                      class="form-stack">

                    <input type="hidden" name="_csrf_token"
                           value="<?= Security::escape(Security::csrfToken()) ?>">

                    <div class="form-field">
                        <label for="email">Endereço de email</label>
                        <input id="email" name="email" type="email"
                               maxlength="150" autocomplete="username"
                               placeholder="exemplo@email.com"
                               value="<?= Security::escape($email) ?>"
                               required autofocus>
                    </div>

                    <div class="form-field">
                        <label for="senha">Palavra-passe</label>
                        <div class="password-control">
                            <input id="senha" name="senha" type="password"
                                   autocomplete="current-password"
                                   placeholder="Introduz a tua palavra-passe"
                                   required>
                        </div>
                    </div>

                    <button class="btn btn-primary btn-full" type="submit">
                        <span>Entrar na plataforma</span>
                        <span class="button-arrow" aria-hidden="true">→</span>
                    </button>
                </form>

                <div class="auth-divider">
                    <span>PARTICIPA NA MUDANÇA</span>
                </div>

                <p class="auth-switch">
                    Ainda não tens conta?
                    <a href="<?= Security::escape($baseUrl) ?>/registo">
                        Criar conta <span aria-hidden="true">↗</span>
                    </a>
                </p>

                <p class="auth-legal">
                    Ao continuares, estás a aceder à plataforma de participação cívica SINC.
                </p>
            </div>
        </section>

    </main>
</div>
