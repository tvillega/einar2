<?php

require_once __DIR__ . '../../../vendor/Michelf/MarkdownExtra.inc.php';

$advguideIndex  = true;
$markdownText = file_get_contents(__DIR__ . '/' . basename(__FILE__, '.php') . '.md');

include(__DIR__ . '/../assets/header.php');
echo Michelf\MarkdownExtra::defaultTransform($markdownText);
include(__DIR__ . '/../assets/footer.php');

?>
