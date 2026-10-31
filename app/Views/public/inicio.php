
<?php
$baseUrl = rtrim((string) ($baseUrl ?? '/sinc'), '/');
?>

<header class="site-header">
    <nav class="site-nav container" aria-label="Navegação principal">
        <a class="site-brand" href="<?= Security::escape($baseUrl) ?>/">
            <span class="site-brand-mark">S</span>
            <span class="site-brand-copy">
                <strong>SINC</strong>
                <small>INTELIGÊNCIA CÍVICA</small>
            </span>
        </a>

        <button class="menu-toggle"
                type="button"
                aria-label="Abrir menu"
                aria-expanded="false"
                aria-controls="site-navigation">
            <span></span><span></span><span></span>
        </button>

        <div class="site-navigation" id="site-navigation">
            <a href="#missao">A nossa missão</a>
            <a href="#como-funciona">Como funciona</a>
            <a href="#areas">Áreas de impacto</a>
            <div class="nav-actions">
                <a class="button button-quiet"
                   href="<?= Security::escape($baseUrl) ?>/login">
                    Entrar
                </a>
                <a class="button button-primary"
                   href="<?= Security::escape($baseUrl) ?>/registo">
                    Participar <span aria-hidden="true">↗</span>
                </a>
            </div>
        </div>
    </nav>
</header>

<main>
    <section class="hero">
        <div class="hero-image" role="img"
             aria-label="Vista urbana de Luanda, representando as comunidades angolanas"></div>
        <div class="hero-shade"></div>

        <div class="container hero-content">
            <div class="hero-copy">
                <span class="eyebrow eyebrow-light">
                    <span class="status-dot"></span>
                    A participação cidadã começa aqui
                </span>

                <h1>
                    A mudança que queremos
                    <span>começa connosco.</span>
                </h1>

                <p>
                    Cada comunidade tem desafios. Cada cidadão tem voz.
                    O SINC aproxima as pessoas dos problemas reais e cria
                    espaço para acompanhar aquilo que precisa de mudar.
                </p>

                <div class="hero-actions">
                    <a class="button button-primary button-large"
                       href="<?= Security::escape($baseUrl) ?>/registo">
                        Quero participar <span aria-hidden="true">↗</span>
                    </a>
                    <a class="button button-glass button-large"
                       href="#missao">
                        Conhecer o SINC <span aria-hidden="true">↓</span>
                    </a>
                </div>

                <div class="hero-note">
                    <span class="hero-note-icon" aria-hidden="true">✳</span>
                    <span>Uma voz pode começar uma conversa.
                    Muitas vozes podem dar força à mudança.</span>
                </div>
            </div>

            <a class="hero-scroll" href="#missao">
                <span>Descobre o que podemos construir juntos</span>
                <span aria-hidden="true">↓</span>
            </a>
        </div>
    </section>

    <section class="intro-section section-space" id="missao">
        <div class="container intro-grid">
            <div class="section-kicker">
                <span class="eyebrow">O propósito</span>
                <span class="kicker-line"></span>
                <span class="kicker-number">01 / 04</span>
            </div>

            <div class="intro-copy">
                <h2>
                    Uma comunidade mais forte começa quando
                    <span>ninguém fica indiferente.</span>
                </h2>
                <p class="lead">
                    Uma estrada degradada. Um bairro sem iluminação.
                    Um problema de saneamento. Situações que afectam
                    o dia a dia merecem ser vistas, comunicadas e acompanhadas.
                </p>
                <p>
                    O SINC é uma plataforma de participação cívica concebida
                    para ajudar os cidadãos a comunicar ocorrências,
                    acompanhar a sua evolução e contribuir para uma visão
                    mais clara dos desafios das comunidades.
                </p>
                <a class="text-link" href="#como-funciona">
                    Descobrir como funciona <span aria-hidden="true">→</span>
                </a>
            </div>

            <div class="intro-visual">
                <div class="intro-photo"></div>
                <div class="quote-card">
                    <span class="quote-mark" aria-hidden="true">“</span>
                    <p>O lugar onde vivemos também é responsabilidade nossa.</p>
                    <span class="quote-caption">PARTICIPAÇÃO · COMUNIDADE · ACÇÃO</span>
                </div>
                <span class="visual-decoration" aria-hidden="true">SINC.</span>
            </div>
        </div>
    </section>

    <section class="areas-section section-space" id="areas">
        <div class="container">
            <div class="section-heading">
                <div>
                    <span class="eyebrow">O que nos move</span>
                    <h2>Os problemas têm lugar.<br><span>A participação também.</span></h2>
                </div>
                <p>
                    O SINC foi pensado para apoiar a comunicação de problemas
                    que afectam os serviços e os espaços da vida colectiva.
                </p>
            </div>

            <div class="areas-grid">
                <article class="area-card area-card-featured">
                    <div class="area-image area-roads" role="img"
                         aria-label="Rua urbana"></div>
                    <div class="area-card-content">
                        <span class="area-number">01</span>
                        <span class="area-icon" aria-hidden="true">⌁</span>
                        <h3>Estradas e mobilidade</h3>
                        <p>Buracos, vias degradadas e dificuldades de circulação.</p>
                        <span class="area-tag">ESPAÇO PÚBLICO</span>
                    </div>
                </article>

                <article class="area-card">
                    <div class="area-image area-light" role="img"
                         aria-label="Rua e iluminação urbana"></div>
                    <div class="area-card-content">
                        <span class="area-number">02</span>
                        <span class="area-icon" aria-hidden="true">☼</span>
                        <h3>Iluminação pública</h3>
                        <p>Locais que precisam de melhor iluminação e segurança.</p>
                        <span class="area-tag">INFRA-ESTRUTURAS</span>
                    </div>
                </article>

                <article class="area-card">
                    <div class="area-image area-sanitation" role="img"
                         aria-label="Ambiente urbano e saneamento"></div>
                    <div class="area-card-content">
                        <span class="area-number">03</span>
                        <span class="area-icon" aria-hidden="true">≈</span>
                        <h3>Saneamento e ambiente</h3>
                        <p>Resíduos, drenagem e questões ambientais locais.</p>
                        <span class="area-tag">QUALIDADE DE VIDA</span>
                    </div>
                </article>

                <article class="area-card">
                    <div class="area-image area-health" role="img"
                         aria-label="Serviços comunitários"></div>
                    <div class="area-card-content">
                        <span class="area-number">04</span>
                        <span class="area-icon" aria-hidden="true">＋</span>
                        <h3>Saúde e educação</h3>
                        <p>Problemas de serviços essenciais que merecem atenção.</p>
                        <span class="area-tag">SERVIÇOS ESSENCIAIS</span>
                    </div>
                </article>
            </div>

            <p class="section-footnote">
                Estas categorias ilustram os tipos de problemas que o SINC
                pretende apoiar. A disponibilidade depende dos registos e
                das funcionalidades activas na plataforma.
            </p>
        </div>
    </section>

    <section class="process-section section-space" id="como-funciona">
        <div class="container">
            <div class="section-heading process-heading">
                <div>
                    <span class="eyebrow">Simples por natureza</span>
                    <h2>Da observação à <span>participação.</span></h2>
                </div>
                <p>
                    Uma experiência clara para que comunicar um problema
                    não seja mais complicado do que explicar o que acontece.
                </p>
            </div>

            <div class="process-grid">
                <article class="process-card">
                    <span class="process-step">PASSO 01</span>
                    <div class="process-icon" aria-hidden="true">◎</div>
                    <h3>Identifica</h3>
                    <p>Observa uma situação que afecta a tua comunidade e
                    reúne informação útil sobre o local e o problema.</p>
                    <span class="process-arrow" aria-hidden="true">↗</span>
                </article>

                <article class="process-card">
                    <span class="process-step">PASSO 02</span>
                    <div class="process-icon" aria-hidden="true">↗</div>
                    <h3>Comunica</h3>
                    <p>Utiliza a plataforma para registar a ocorrência com
                    uma descrição clara e os detalhes disponíveis.</p>
                    <span class="process-arrow" aria-hidden="true">↗</span>
                </article>

                <article class="process-card">
                    <span class="process-step">PASSO 03</span>
                    <div class="process-icon" aria-hidden="true">◷</div>
                    <h3>Acompanha</h3>
                    <p>Consulta a informação e acompanha as actualizações
                    disponíveis sobre a evolução da ocorrência.</p>
                    <span class="process-arrow" aria-hidden="true">↗</span>
                </article>
            </div>
        </div>
    </section>

    <section class="closing-section">
        <div class="container closing-inner">
            <div class="closing-orbit" aria-hidden="true">
                <span class="orbit orbit-one"></span>
                <span class="orbit orbit-two"></span>
                <span class="orbit-core">S</span>
                <span class="orbit-point orbit-point-one"></span>
                <span class="orbit-point orbit-point-two"></span>
                <span class="orbit-point orbit-point-three"></span>
            </div>

            <div class="closing-copy">
                <span class="eyebrow eyebrow-light">O próximo passo é teu</span>
                <h2>Angola não se transforma apenas com palavras. <span>Transforma-se com participação.</span></h2>
                <p>
                    Faz parte de uma cultura em que observar, comunicar e
                    acompanhar os problemas é um compromisso com o lugar
                    onde vivemos.
                </p>
                <div class="hero-actions">
                    <a class="button button-primary button-large"
                       href="<?= Security::escape($baseUrl) ?>/registo">
                        Criar a minha conta <span aria-hidden="true">↗</span>
                    </a>
                    <a class="button button-glass button-large"
                       href="<?= Security::escape($baseUrl) ?>/login">
                        Já tenho conta
                    </a>
                </div>
            </div>
        </div>
    </section>
</main>

<footer class="site-footer">
    <div class="container footer-main">
        <a class="site-brand footer-brand"
           href="<?= Security::escape($baseUrl) ?>/">
            <span class="site-brand-mark">S</span>
            <span class="site-brand-copy">
                <strong>SINC</strong>
                <small>INTELIGÊNCIA CÍVICA</small>
            </span>
        </a>

        <p>
            Participação cidadã, informação e responsabilidade colectiva.
        </p>

        <div class="footer-links">
            <a href="#missao">A missão</a>
            <a href="#como-funciona">Como funciona</a>
            <a href="<?= Security::escape($baseUrl) ?>/login">Entrar</a>
            <a href="<?= Security::escape($baseUrl) ?>/registo">Registar</a>
        </div>
    </div>

    <div class="container footer-bottom">
        <span>© <?= date('Y') ?> SINC · Inteligência cívica</span>
        <a href="#top" class="back-to-top">Voltar ao início ↑</a>
    </div>
</footer>
