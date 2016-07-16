<?php
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestUri = preg_replace('/\?.+$/', '', $requestUri);
$requestUri = preg_replace('!/+!', '/', trim($requestUri));
$requestUri = trim($requestUri, '/');
if (!preg_match('!^([2-7a-z]{2}/[2-7a-z]{26})\.jpg$!', $requestUri, $match)) {
    error404();
    exit();
}

$jpgRealPath = realpath(dirname(__DIR__) . '/src') . '/' . $match[1] . '.jpg';
$lepRealPath = realpath(dirname(__DIR__) . '/src') . '/' . $match[1] . '.lep';

if (file_exists($jpgRealPath) && is_file($jpgRealPath) && is_readable($jpgRealPath)) {
    send(file_get_contents($jpgRealPath));
} elseif (file_exists($lepRealPath) && is_file($lepRealPath) && is_readable($lepRealPath)) {
    send(decodeLep(file_get_contents($lepRealPath)));
} else {
    error404();
}
exit();

function error404()
{
    if (!headers_sent()) {
        header('HTTP/1.1 404 Not Found');
        header('Content-Type: text/plain');
    }
    echo "not found\n";
    exit();
}

function decodeLep(string $lepBinary) : string
{
    $descSpec = [
        ['pipe', 'r'],
        ['pipe', 'w'],
    ];
    if (!$proc = @proc_open('/usr/bin/lepton - 2>/dev/null', $descSpec, $pipes)) {
        if (!headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error');
        }
        echo "internal server error (fork)\n";
        exit();
    }
    fwrite($pipes[0], $lepBinary);
    fclose($pipes[0]);
    $jpegBinary = stream_get_contents($pipes[1]);
    fclose($pipes[1]);

    if (proc_close($proc) !== 0) {
        if (!headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error');
        }
        echo "internal server error (status)\n";
        exit();
    }

    return $jpegBinary;
}

function send(string $jpegBinary)
{
    if (!headers_sent()) {
        header('Cache-Control: max-age=' . (7 * 86400) . ', no-transform');
    }
    if (function_exists('imagewebp')) {
        if (!headers_sent()) {
            header('Vary: Accept');
        }
        $accept = (string)($_SERVER['HTTP_ACCEPT'] ?? '*/*');
        if (strpos($accept, 'image/webp') !== false) {
            if ($gd = @imagecreatefromstring($jpegBinary)) {
                header('Content-Type: image/webp');
                imagewebp($gd, null); // put WebP
                imagedestroy($gd);
                return;
            }
        }
    }
    if (!headers_sent()) {
        header('Content-Type: image/jpeg');
    }
    echo $jpegBinary;
}
