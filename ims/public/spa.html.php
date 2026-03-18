<?php

/**
 * Invyte - Professional Edition
 * Invyte is an open-source inventory management system designed for Magento 2
 * Version: 1.0.0
 * Kenneth D'silva (Modracx), Copyright (c) March 2026
 * Licensed under the MIT License – https://opensource.org/licenses/MIT
 */

$manifestPath = __DIR__ . '/assets/.vite/manifest.json';
$jsFile = '';
$cssFile = '';

if (file_exists($manifestPath)) {
    $manifest = json_decode(file_get_contents($manifestPath), true);
    $entry = $manifest['main.js'] ?? $manifest[array_key_first($manifest)] ?? null;
    if ($entry) {
        $jsFile = '/ims/assets/' . $entry['file'];
        foreach (($entry['css'] ?? []) as $css) {
            $cssFile = '/ims/assets/' . $css;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invyte - Inventory Management System</title>
    <?php if ($cssFile): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($cssFile) ?>">
    <?php endif; ?>
</head>
<body>
    <div id="app"></div>
    <?php if ($jsFile): ?>
    <script type="module" src="<?= htmlspecialchars($jsFile) ?>"></script>
    <?php endif; ?>
</body>
</html>
