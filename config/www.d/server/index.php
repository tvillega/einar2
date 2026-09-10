<?php

header('Content-Type: text/plain; charset=utf-8');

$serverIp = !empty($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR'] !== '127.0.0.1' 
    ? $_SERVER['SERVER_ADDR'] 
    : gethostbyname(gethostname());

echo "\n";
echo "  Server Address  : " . $serverIp . "\n";
echo "  Server Port     : " . $_SERVER['SERVER_PORT'] . "\n";
echo "  Client Address  : " . $_SERVER['REMOTE_ADDR'] . "\n";
echo "  Client Port     : " . $_SERVER['REMOTE_PORT'] . "\n";
echo "\n";

echo "  Request Method  : " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "  Request URI     : " . $_SERVER['REQUEST_URI'] . "\n";
echo "  Protocol Version: " . $_SERVER['SERVER_PROTOCOL'] . "\n";
echo "\n";

$headers = function_exists('getallheaders') ? getallheaders() : [];
if (empty($headers)) {
    foreach ($_SERVER as $name => $value) {
        if (substr($name, 0, 5) == 'HTTP_') {
            $headerName = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
            $headers[$headerName] = $value;
        }
    }
}

foreach ($headers as $key => $val) {
    echo "  " . $key . ": " . $val . "\n";
}
echo "\n";
