<?php

require_once __DIR__ . '../../vendor/Michelf/MarkdownExtra.inc.php';

$rootIndex      = true;
$markdownText   = file_get_contents(__DIR__ . '/assets/index.md');

include(__DIR__ . '/assets/header.php');
echo Michelf\MarkdownExtra::defaultTransform($markdownText);
include(__DIR__ . '/assets/footer.php');

?>
