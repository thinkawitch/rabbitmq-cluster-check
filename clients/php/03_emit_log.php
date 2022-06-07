<?php

require_once '00_common.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

[$host, $user, $pass] = getHostUserPass();

$connection = new AMQPStreamConnection($host, 5672, $user, $pass);
$channel = $connection->channel();

$channel->exchange_declare('logs', 'fanout', false, false, false);

$data = implode(' ', array_slice($argv, 1));
if (empty($data)) {
    $data = "info: Hello World!";
}
$msg = new AMQPMessage($data);

$channel->basic_publish($msg, 'logs');

echo ' [x] Sent ', $data, "\n";

$channel->close();
$connection->close();
