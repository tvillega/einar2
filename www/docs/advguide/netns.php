<?php

require_once __DIR__ . '../../vendor/Michelf/MarkdownExtra.inc.php';

$markdownText = file_get_contents(__DIR__ . '/md/netns.md');
$htmlContent = Michelf\MarkdownExtra::defaultTransform($markdownText);

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Einar2</title>
        <style>
        body {
            padding: 20px;
        }
        code {
          background-color: #F0F8FF;
        }
        .topnav {
          background-color: #333;
          overflow: hidden;
        }
        .topnav a {
          float: center;
          color: #f2f2f2;
          text-align: center;
          padding: 2px 6px;
          text-decoration: none;
          font-size: 17px;
        }
        .topnav a:hover {
          background-color: #ddd;
          color: black;
        }
        .topnav a.active {
          background-color: #04AA6D;
          color: white;
        }
        .image-container {
          float: left;
          margin-right: 20px;
        }
        .header-frame {
          display: flex;
          align-items: center;
          top: 10%;
          left: 50%;
        }
        .image-frame {
          display: inline-block;
          background: url("digging.png") no-repeat center;
          width: calc(4vw + 4vh);
          height: calc(4vw + 4vh);
          background-size: cover;
        }
        .text-frame {
          font-size: calc(2.5vw + 2.5vh);
          display: inline-block;
          margin: 1vh 1vw;
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
                    <a href="/index.php">Home</a>
                    <a href="#userguide">User guide</a>
                    <a class="active" href="#advanceduserguide">Advanced user guide</a>
                    <a href="#links">Links</a>
                    <a href="#download">Download</a>
                    <a href="#contact">Contact</a>
                </div>
            </center>
        </div>



        <div>
            <?php echo $htmlContent; ?>
        </div>
    </body>
</html>
