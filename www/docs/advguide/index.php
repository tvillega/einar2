<?php

require_once __DIR__ . '/../assets/docsHelper.php';

$advguideIndex = true;

$docsDir   = __DIR__;
$docs      = sortDocs(loadDocs($docsDir));
$requested = resolveRequestedSlug($_SERVER['QUERY_STRING'] ?? '');
$isIndex   = ($requested === null);
$html      = !$isIndex ? renderDocPage($docsDir, $requested) : '';

include __DIR__ . '/../assets/header.php';
?>

<?php if ($isIndex): ?>
    <h2>Index</h2>
    <ul>
    <?php foreach ($docs as $doc): ?>
        <li><a href="?<?= htmlspecialchars(rawurlencode($doc['slug']), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($doc['title'], ENT_QUOTES, 'UTF-8') ?></a></li>
    <?php endforeach; ?>
    </ul>
<?php else: ?>
    <?= $html ?>
    <hr>
    <p><a href="index.php">&laquo; back to the index</a></p>
<?php endif; ?>

<?php include __DIR__ . '/../assets/footer.php'; ?>
