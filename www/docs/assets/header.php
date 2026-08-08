<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>Einar2</title>
        <link
            rel="stylesheet"
            type="text/css"
            href="<?php echo (isset($rootIndex)) ? 'assets/stylesheet.css' : '../assets/stylesheet.css'; ?>"
        >
        <style>
            .image-frame {
              display: inline-block;
              background: url("<?php echo (isset($rootIndex)) ? 'assets/logo.png' : '../assets/logo.png'; ?>") no-repeat center;
              width: calc(0vw + 50vh);
              height: calc(0vw + 20vh);
              background-size: cover;
            }
        </style>
    </head>
    <body>

        <header>

            <div class="header-frame">
                <center class="center">
                    <span class="image-frame"></span>
                </center>
            </div>

            <?php $clsNav = 'class="active"'; ?>

            <center>
                <div class="topnav">
                    <a <?php echo (isset($rootIndex))      ? $clsNav : ''; ?> href="/docs/index.php">Home</a>
                    <a <?php echo (isset($userguideIndex)) ? $clsNav : ''; ?> href="/docs/userguide/index.php">User guide</a>
                    <a <?php echo (isset($advguideIndex))  ? $clsNav : ''; ?> href="/docs/advguide/index.php">Advanced user guide</a>
                    <a <?php echo (isset($linksIndex))     ? $clsNav : ''; ?> href="/docs/links/index.php">Links</a>
                    <a <?php echo (isset($downloadIndex))  ? $clsNav : ''; ?> href="/docs/download/index.php">Download</a>
                    <a <?php echo (isset($contactIndex))   ? $clsNav : ''; ?> href="/docs/contact/index.php">Contact</a>
                </div>
            </center>

        </header>

        <?php
            $fileTitle = '';
            if (isset($rootIndex)):
              $fileTitle = "Welcome to Einar2";
            elseif (isset($userguideIndex)):
              $fileTitle = "User guide";
            elseif (isset($advguideIndex)):
              $fileTitle = "Advanced user guide";
            elseif (isset($linksIndex)):
              $fileTitle = "Links";
            elseif (isset($downloadIndex)):
              $fileTitle = "Download";
            elseif (isset($contactIndex)):
              $fileTitle = "Contact";
            endif;
            echo "<center><h1>" . $fileTitle . "</h1></center>";
        ?>
