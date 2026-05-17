<?php

require_once __DIR__ . "../../../vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../../");
$dotenv->load();

define("PADDLE_ENVIRONMENT", $_ENV["PADDLE_ENVIRONMENT"] ?? "sandbox");
define("PADDLE_CLIENT_TOKEN", $_ENV["PADDLE_CLIENT_TOKEN"] ?? "");
define("PADDLE_PRICE_ID", $_ENV["PADDLE_PRICE_ID"] ?? "");
define("PADDLE_CURRENCY", $_ENV["PADDLE_CURRENCY"] ?? "EUR");
define("PADDLE_DESCRIPTION", $_ENV["PADDLE_DESCRIPTION"] ?? "Paddle Test Payment");

?>