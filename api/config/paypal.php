<?php

require_once __DIR__ . "../../../vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../../");
$dotenv->load();

define("PAYPAL_CLIENT_ID",     $_ENV['PAYPAL_CLIENT_ID']);
define("PAYPAL_CLIENT_SECRET", $_ENV['PAYPAL_CLIENT_SECRET']);
define("PAYPAL_BASE_URL",      $_ENV['PAYPAL_BASE_URL']);

?>