
<?php
$pageTitle = 'Criar conta';
$errors = $errors ?? [];
$old = $old ?? [];
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
                    FAZ PARTE DA MUDANÇA
                </span>

                <h2>A tua comunidade merece <span>ser ouvida.</span></h2>

                <p>
                    Cria a tua conta e junta-te a uma comunidade que acredita
                    na participação, na responsabilidade e na mudança.
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
                        <strong>Juntos, fazemos a diferença.</strong>
                        <span>A participação de cada cidadão conta.</span>
                    </div>
                </div>
            </div>

            <div class="showcase-footer">
                <span>Transparência. Participação. Mudança.</span>
                <span class="showcase-footer-line"></span>
            </div>
        </aside>

        <section class="auth-panel auth-panel-register">
            <div class="auth-panel-inner">

                <div class="auth-mobile-brand">
                    <span class="auth-brand-mark">S</span>
                    <span>SINC<span class="auth-brand-dot">.</span></span>
                </div>

                <div class="auth-heading">
                    <span class="eyebrow">NOVO MEMBRO SINC</span>
                    <h1>Cria a tua <span>conta.</span></h1>
                    <p>Preenche os teus dados para começares a participar.</p>
                </div>

                <?php if (!empty($errors['formulario'])): ?>
                    <div class="alert alert-error" role="alert">
                        <span class="alert-icon">!</span>
                        <span><?= Security::escape($errors['formulario']) ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST"
                      action="<?= Security::escape($baseUrl) ?>/registo"
                      class="form-stack register-form">

                    <input type="hidden" name="_csrf_token"
                           value="<?= Security::escape(Security::csrfToken()) ?>">

                    <div class="register-grid">

                        <div class="form-field field-name">
                            <label for="nome_completo">Nome completo</label>
                            <input id="nome_completo" name="nome_completo"
                                   type="text" maxlength="150" autocomplete="name"
                                   placeholder="O teu nome completo"
                                   value="<?= Security::escape($old['nome_completo'] ?? '') ?>"
                                   required>
                            <?php if (isset($errors['nome_completo'])): ?>
                                <small class="field-error">
                                    <?= Security::escape($errors['nome_completo']) ?>
                                </small>
                            <?php endif; ?>
                        </div>

                        <div class="form-field field-email">
                            <label for="email">Endereço de email</label>
                            <input id="email" name="email" type="email"
                                   maxlength="150" autocomplete="email"
                                   placeholder="exemplo@email.com"
                                   value="<?= Security::escape($old['email'] ?? '') ?>"
                                   required>
                            <?php if (isset($errors['email'])): ?>
                                <small class="field-error">
                                    <?= Security::escape($errors['email']) ?>
                                </small>
                            <?php endif; ?>
                        </div>

                        <div class="form-field field-phone">
                            <label for="telefone">
                                Telefone <span class="optional">(opcional)</span>
                            </label>
                            <input id="telefone" name="telefone" type="tel"
                                   maxlength="30" autocomplete="tel"
                                   placeholder="+244 9XX XXX XXX"
                                   value="<?= Security::escape($old['telefone'] ?? '') ?>">
                            <?php if (isset($errors['telefone'])): ?>
                                <small class="field-error">
                                    <?= Security::escape($errors['telefone']) ?>
                                </small>
                            <?php endif; ?>
                        </div>

                        <div class="form-field field-password">
                            <label for="senha">Palavra-passe</label>
                            <div class="password-control">
                                <input id="senha" name="senha" type="password"
                                       minlength="12" autocomplete="new-password"
                                       placeholder="Mínimo de 12 caracteres"
                                       required>
                            </div>
                            <?php if (isset($errors['senha'])): ?>
                                <small class="field-error">
                                    <?= Security::escape($errors['senha']) ?>
                                </small>
                            <?php endif; ?>
                            <small class="field-hint">
                                Pelo menos 12 caracteres.
                            </small>
                        </div>

                        <div class="form-field field-confirm-password">
                            <label for="confirmar_senha">Confirmar palavra-passe</label>
                            <div class="password-control">
                                <input id="confirmar_senha" name="confirmar_senha"
                                       type="password" minlength="12"
                                       autocomplete="new-password"
                                       placeholder="Repete a palavra-passe"
                                       required>
                            </div>
                            <?php if (isset($errors['confirmar_senha'])): ?>
                                <small class="field-error">
                                    <?= Security::escape($errors['confirmar_senha']) ?>
                                </small>
                            <?php endif; ?>
                        </div>

                    </div>

                    <button class="btn btn-primary btn-full" type="submit">
                        <span>Criar a minha conta</span>
                        <span class="button-arrow" aria-hidden="true">→</span>
                    </button>
                </form>

                <p class="auth-switch">
                    Já tens uma conta?
                    <a href="<?= Security::escape($baseUrl) ?>/login">
                        Iniciar sessão <span aria-hidden="true">↗</span>
                    </a>
                </p>

                <p class="auth-legal">
                    Os teus dados são tratados de acordo com as regras da plataforma.
                </p>
            </div>
        </section>

    </main>
</div>
