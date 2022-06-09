<?php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . './../../');
$dotenv->load();

function getRMQHostPortUserPass(): array {
    $host = $_ENV['HAPROXY_HOST'];
    $port = $_ENV['HAPROXY_PORT'];
    #$host = explode('@', $_ENV['RABBIT_1_NODENAME'])[1];
    #$port = $_ENV['RABBIT_1_PORT'];
    $user = $_ENV['RABBIT_USER'];
    $pass = $_ENV['RABBIT_PASS'];
    return [$host, $port, $user, $pass];
}
