<?php

require_once __DIR__ . '/vendor/Michelf/MarkdownExtra.inc.php';

$markdownText = file_get_contents(__DIR__ . '/docs/md/releasenotes.md');
$htmlContent = Michelf\MarkdownExtra::defaultTransform($markdownText);

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Release Notes</title>
            <style>
        body {
            padding: 20px;
        }
    </style>
    </head>
    <body>
        <div>
            <?php echo $htmlContent; ?>
        </div>
    </body>
</html>
