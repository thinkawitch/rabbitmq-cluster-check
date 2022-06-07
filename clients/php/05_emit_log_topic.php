<?php

require_once '00_common.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

[$host, $user, $pass] = getHostUserPass();

$connection = new AMQPStreamConnection($host, 5672, $user, $pass);
$channel = $connection->channel();

$channel->exchange_declare('topic_logs', 'topic', false, false, false);

$routing_key = isset($argv[1]) && !empty($argv[1]) ? $argv[1] : 'anonymous.info';
$data = implode(' ', array_slice($argv, 2));
if (empty($data)) {
    $data = "Hello World!";
}

$msg = new AMQPMessage($data);

$channel->basic_publish($msg, 'topic_logs', $routing_key);

echo ' [x] Sent ', $routing_key, ':', $data, "\n";

$channel->close();
$connection->close();
