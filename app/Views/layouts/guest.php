
<?php
$pageTitle = $pageTitle ?? 'Acesso';
$baseUrl = $baseUrl ?? '/sinc';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#173B57">
    <title><?= Security::escape($pageTitle) ?> | SINC</title>
    <!-- <link rel="stylesheet"
          href="<?= Security::escape($baseUrl) ?>/public/assets/css/app.css"> -->
          
    <link rel="stylesheet"
          href="<?= Security::escape($baseUrl) ?>/public/assets/css/auth.css">

</head>
<body>
        <section class="auth-card">
            <?= $content ?>
        </section>
    
<script src="<?= Security::escape($baseUrl) ?>/assets/js/auth.js" defer></script>

</body>
</html>
