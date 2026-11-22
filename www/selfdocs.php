<?php

require_once __DIR__ . '/vendor/Michelf/MarkdownExtra.inc.php';

if (!isset($_GET['laboratory']) || $_GET['laboratory'] === '') {
    die('Missing mandatory "laboratory" query parameter.');
}

$labsRoot = $_SERVER['DOCUMENT_ROOT'] . '/labs';
$labName  = $_GET['laboratory'];
$pageName = $_GET['page'] ?? null;

$isIndex  = ($pageName === null || $pageName === '');

$docsDir  = $labsRoot . '/' . $labName . '/docs/';

$files    = [];
$html     = '';

$docsExists = is_dir($docsDir);

if ($docsExists) {
    foreach (scandir($docsDir) as $f) {
        if ($f[0] === '.') {
            continue;
        }
        if (is_file($docsDir . '/' . $f) && strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'md') {
            $files[] = $f;
        }
    }
    sort($files, SORT_NATURAL | SORT_FLAG_CASE);
}

$emptyDocs = empty($files);
$pageTitle = $labName . "'s docs";

if (!$isIndex) {
    if (!$docsExists) {
        $html = '<p>Documentation directory does not exist.</p>';
    } else {
        $safePage = basename($pageName);
        $pagePath = $docsDir . '/' . $safePage;

        if (!in_array($safePage, $files, true) || !file_exists($pagePath)) {
            $html = '<p>Requested documentation page was not found.</p>';
        } else {
            $markdownText = file_get_contents($pagePath);
            $html         = Michelf\MarkdownExtra::defaultTransform($markdownText);
            $pageTitle    = '[' . $labName . '] ' . pathinfo($safePage, PATHINFO_FILENAME);
        }
    }
}

header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title><?= htmlspecialchars($pageTitle) ?></title>
        <link
                rel="stylesheet"
                type="text/css"
                href="docs/assets/stylesheet.css"
        >
    </head>
<body>

<?php
    $queryStringLab = '?laboratory=' . urlencode($labName);

    if ($isIndex):
?>

        <h1><?= htmlspecialchars($labName) ?>'s documentation</h1>

<?php
        if (!$docsExists):
?>
            <p>No documentation directory found for this laboratory.</p>
<?php
        elseif ($emptyDocs):
?>
            <p>No documentation available.</p>
<?php
        else:
?>
            <ul>
            <?php foreach ($files as $f): ?>
                <?php $queryStringPage = '&page=' . urlencode($f); ?>
                <li>
                    <a href="<?= htmlspecialchars($queryStringLab . $queryStringPage) ?>"><?= htmlspecialchars($f) ?></a>
                </li>
            <?php endforeach; ?>
            </ul>
<?php
        endif;
    else:
        echo $html;
?>

        <hr>
        <p>
            <a href="<?= htmlspecialchars($queryStringLab) ?>">&laquo; back to the index</a>
        </p>

<?php
    endif;
    include(__DIR__ . '/docs/assets/footer.php');
?>
