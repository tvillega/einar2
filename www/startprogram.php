<?php

$settingsFile  = $_SERVER['DOCUMENT_ROOT'] . '/settings.json';
if (!file_exists($settingsFile)) {
  die("File settings.json missing at document root");
}
$settingsRaw   = file_get_contents($settingsFile);
$settingsData  = json_decode($settingsRaw, true);

if (!$settingsData["einar2"]["jailbreak"]) {
  die("Action forbidden: container isolated from host");
}

$inputPipe  = $_SERVER['DOCUMENT_ROOT'] . '/ch0';
$outputPipe = $_SERVER['DOCUMENT_ROOT'] . '/ch1';

if (isset($_GET["program"])) {
  // exec($_GET["program"] . "  > /dev/null 2>&1 &", $output, $result);s

    $cmd = escapeshellarg($_GET["program"]);
    exec("echo $cmd > " . escapeshellarg($inputPipe) . " 2>" . $outputPipe);

    header('Location: ' . $_SERVER['HTTP_REFERER']);
}

?>
