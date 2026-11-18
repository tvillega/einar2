<?php

$settingsFile  = $_SERVER['DOCUMENT_ROOT'] . '/webui.json';
if (!file_exists($settingsFile)) {
  die("File webui.json missing at document root");
}
$settingsRaw   = file_get_contents($settingsFile);
$settingsData  = json_decode($settingsRaw, true);

if (!$settingsData["einar2"]["jailbreak"]) {
  die("Action forbidden: container isolated from host");
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Setup interfaces</title>
    <style>
        body {
            padding: 20px;
        }
        .form-group {
            padding: 5px 0px;
        }
        .form-group label {
            padding-right: 10px;
        }
        .submit-container {
            padding-top: 15px;
        }
        .output-container {
            padding: 10px;
            text-align: left;
        }
    </style>
</head>
<body>

        <h1>Start a lab</h1>

        <center><p><b>Available laboratories</b></p></center>

        <!-- I have to list the /einar2/labs that should be the DOCUMENT_ROOT I think  -->

        <?php
            echo "<pre>";
            $dirs = scandir($_SERVER["DOCUMENT_ROOT"] . "/labs");
            array_shift($dirs);
            array_shift($dirs);
            echo "</pre>";
        ?>

        <center><p><b>Deployed laboratories</b></p></center>
        <?php
            $command = 'echo exec einar2 labs list all | socat - UNIX-CONNECT:' . escapeshellarg($settingsData["einard"]["socket"]);
            $response = shell_exec($command);

            echo "<pre>";
            echo $response;
            echo "</pre>";

            $phpArray = [];

            if ($response !== null && trim($response) !== '') {
                $phpArray = json_decode($response, true);
            }

            $jsonArray = json_encode($phpArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

            echo "<pre>";
            echo print_r($jsonArray);
            echo "</pre>";
        ?>

        <div style="margin-top: 15px;">
            <a href="/index.php">Home</a>
        </div>
    </body>
</html>
