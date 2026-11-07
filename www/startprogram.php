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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["payload"])) {

    $payload = $_POST["payload"];
    $command = 'echo ' . escapeshellarg($payload) . ' | socat - UNIX-CONNECT:' . escapeshellarg($settingsData["einard"]["socket"]);
    $response = shell_exec($command);

    echo "<pre>";
    echo trim($response);
    echo "</pre>";
  } else {
    die("Invalid request");
  }

?>
