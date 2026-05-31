<?php

$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$projectRoot = realpath(__DIR__);
$requestedFile = realpath(__DIR__ . $path);


if (
    $path !== "/" &&
    $requestedFile &&
    str_starts_with($requestedFile, $projectRoot) &&
    is_file($requestedFile)
) {
    return false;
}


chdir(__DIR__ . "/api");
require __DIR__ . "/api/index.php";