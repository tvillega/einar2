<?php

require_once __DIR__ . '../../../vendor/Michelf/MarkdownExtra.inc.php';

$advguideIndex  = true;
$markdownText = file_get_contents(__DIR__ . '/' . basename(__FILE__, '.php') . '.md');

include(__DIR__ . '/../assets/header.php');

?>

<ul>
    <li><a href="createvm-stage1.php">Creating <i>Stage 1</i> manually</a></li>
    <li><a href="createvm-stage2.php">Creating <i>Stage 2</i> manually</a></li>
</ul>

<?php

echo Michelf\MarkdownExtra::defaultTransform($markdownText);
include(__DIR__ . '/../assets/footer.php');

?>
