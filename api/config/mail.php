<?php

require_once __DIR__ . "../../../vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../../");
$dotenv->load();

define("MAIL_FROM",         $_ENV['MAIL_FROM']);
define("MAIL_APP_PASSWORD", $_ENV['MAIL_APP_PASSWORD']);
define("MAIL_FROM_NAME",    $_ENV['MAIL_FROM_NAME']);
?>