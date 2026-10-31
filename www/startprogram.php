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

if (isset($_GET["program"])) {

    $command = 'echo "' . $_GET["program"] . '" | socat - UNIX-CONNECT:/var/run/einar2.sock';
    $response = shell_exec($command);

    echo "<pre>";
    echo trim($response);
    echo "</pre>";
}

?>
