<?php

require_once __DIR__ . '/../00_common.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

[$host, $port, $user, $pass] = getRMQHostPortUserPass();

$connection = new AMQPStreamConnection($host, $port, $user, $pass);
$channel = $connection->channel();

$channel->queue_declare('task_queue', false, true, false, false);

$data = implode(' ', array_slice($argv, 1));
if (empty($data)) {
    $data = "Hello World!";
}
$msg = new AMQPMessage(
    $data,
    array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
);

$channel->basic_publish($msg, '', 'task_queue');

echo ' [x] Sent ', $data, "\n";

$channel->close();
$connection->close();
