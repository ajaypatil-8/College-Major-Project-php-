<?php

$envPath = __DIR__ . "/../.env";

if (!file_exists($envPath)) {
    die("ERROR: .env file not found");
}

$env = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

foreach ($env as $line) {

    $line = trim($line);

    if (!$line || strpos($line, '#') === 0) {
        continue;
    }

    if (!strpos($line, '=')) continue;

    list($key, $val) = explode('=', $line, 2);

    $key = trim($key);
    $val = trim($val);

    $_ENV[$key] = $val;
    putenv("$key=$val");
}
?>