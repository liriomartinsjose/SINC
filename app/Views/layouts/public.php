
<?php
$pageTitle = $pageTitle ?? 'Participação cidadã';
$baseUrl = rtrim((string) ($baseUrl ?? '/sinc'), '/');

$pageStyles = $pageStyles ?? [];
$pageScripts = $pageScripts ?? [];

if (is_string($pageStyles) && $pageStyles !== '') {
    $pageStyles = [$pageStyles];
}

if (is_string($pageScripts) && $pageScripts !== '') {
    $pageScripts = [$pageScripts];
}

$pageStyles = is_array($pageStyles) ? $pageStyles : [];
$pageScripts = is_array($pageScripts) ? $pageScripts : [];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#102F46">
    <meta name="description"
          content="O SINC aproxima os cidadãos dos problemas das suas comunidades e promove a participação cívica em Angola.">

    <title><?= Security::escape($pageTitle) ?> | SINC</title>

    <link rel="stylesheet"
          href="<?= Security::escape($baseUrl) ?>/public/assets/css/app.css">

    <?php foreach ($pageStyles as $style): ?>
        <?php
        $style = trim((string) $style);

        if ($style === '') {
            continue;
        }

        $styleUrl = preg_match('~^https?://~i', $style)
            ? $style
            : $baseUrl . '/' . ltrim($style, '/');
        ?>
        <link rel="stylesheet"
              href="<?= Security::escape($styleUrl) ?>">
    <?php endforeach; ?>
</head>
<body class="public-page">

    <?= $content ?>

    <?php foreach ($pageScripts as $script): ?>
        <?php
        $script = trim((string) $script);

        if ($script === '') {
            continue;
        }

        $scriptUrl = preg_match('~^https?://~i', $script)
            ? $script
            : $baseUrl . '/' . ltrim($script, '/');
        ?>
        <script src="<?= Security::escape($scriptUrl) ?>" defer></script>
    <?php endforeach; ?>
</body>
</html>
