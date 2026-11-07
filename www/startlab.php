<?php

$settingsFile  = $_SERVER['DOCUMENT_ROOT'] . '/defaults.json';
if (!file_exists($settingsFile)) {
  die("File defaults.json missing at document root");
}
$settingsRaw   = file_get_contents($settingsFile);
$settingsData  = json_decode($settingsRaw, true);

if (!$settingsData["einar2"]["jailbreak"]) {
  die("Action forbidden: container isolated from host");
}

$command = 'echo "run list-builtin-labs" | socat - UNIX-CONNECT:' . escapeshellarg($settingsData["einard"]["socket"]);
$output = shell_exec($command);

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

        <center><p><b>Built-in laboratories</b></p></center>
        <?php
          $array = array_filter(explode("\n", $output));
          echo "<pre>";
          echo print_r($array);
          echo "</pre>";
        ?>
        <center><p><b>Your laboratories</b></p></center>
        <?php
          echo "<pre>";
          $dirs = scandir($_SERVER["DOCUMENT_ROOT"] . "/labs");
          array_shift($dirs);
          array_shift($dirs);
          echo print_r($dirs);
          echo "</pre>";
        ?>

        <div style="margin-top: 15px;">
            <a href="/index.php">Home</a>
        </div>
    </body>
</html>
