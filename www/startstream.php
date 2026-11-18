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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["payload"])) {
    $payload = $_POST["payload"];
    $command = 'echo ' . escapeshellarg($payload) . ' | socat - UNIX-CONNECT:' . escapeshellarg($settingsData["einard"]["socket"]);

    // Disable output buffering to allow real-time chunk streaming
    if (function_exists('apache_setenv')) {
        apache_setenv('no-gzip', '1');
    }
    ini_set('output_buffering', 'off');
    ini_set('zlib.output_compression', false);

    // Set headers for plain text chunked stream
    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: no-cache');
    header('X-Accel-Buffering: no'); // Disables Nginx output buffering if present

    // Clear any existing output buffers
    while (ob_get_level() > 0) {
        ob_end_flush();
    }
    flush();

    // Open process handle and read/flush line by line live
    $handle = popen($command, 'r');
    if ($handle) {
        while (!feof($handle)) {
            $buffer = fgets($handle);
            if ($buffer !== false) {
                echo $buffer;
                flush();
            }
        }
        pclose($handle);
    }
} else {
    die("Invalid request");
}

?>
