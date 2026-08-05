<?php

require_once __DIR__ . '../../../vendor/Michelf/MarkdownExtra.inc.php';

$markdownText = file_get_contents(__DIR__ . '/md/index.md');
$htmlContent = Michelf\MarkdownExtra::defaultTransform($markdownText);

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Einar2</title>
        <link rel="stylesheet" type="text/css" href="../stylesheet.css">
        <style>
            .image-frame {
              display: inline-block;
              background: url("../digging.png") no-repeat center;
              width: calc(4vw + 4vh);
              height: calc(4vw + 4vh);
              background-size: cover;
            }
        </style>
    </head>
    <body>

        <div>

            <div>
                <div class="header-frame">
                    <span class="image-frame"></span>
                    <h5 class="text-frame">Einar2</h5><br>
                </div>
            </div>

            <center>
                <div class="topnav">
                <a href="/docs/index.php">Home</a>
                <a href="/docs/userguide/index.php">User guide</a>
                <a href="/docs/advguide/index.php">Advanced user guide</a>
                <a class="active" href="/docs/links/index.php">Links</a>
                <a href="/docs/download/index.php">Download</a>
                <a href="/docs/contact/index.php">Contact</a>
                </div>
            </center>
        </div>



        <div>
            <?php echo $htmlContent; ?>
        </div>
    </body>
</html>
