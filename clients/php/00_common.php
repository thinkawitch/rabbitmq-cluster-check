<?php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . './../../');
$dotenv->load();

function getHostUserPass(): array {
    $host = $_ENV['HAPROXY_HOST'];
    #$host = explode('@', $_ENV['RABBIT_1_NODENAME'])[1];
    $user = $_ENV['RABBIT_USER'];
    $pass = $_ENV['RABBIT_PASS'];
    return [$host, $user, $pass];
}
