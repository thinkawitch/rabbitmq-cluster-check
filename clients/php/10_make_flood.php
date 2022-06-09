<?php

require_once '00_common.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$mtStart = microtime(true);

[$host, $port, $user, $pass] = getRMQHostPortUserPass();

$connection = new AMQPStreamConnection($host, $port, $user, $pass);
$channel = $connection->channel();

$channel->queue_declare('flood_queue', false, false, false, false);

$limit = 1_000_000;
for ($i=1; $i<=$limit; $i++) {
    $msg = new AMQPMessage(uniqid('message-', true));
    $channel->basic_publish($msg, '', 'flood_queue');
}

$channel->close();
$connection->close();

$mtEnd = microtime(true);
$seconds = $mtEnd - $mtStart;

echo " [x] Sent $limit messages in {$seconds} seconds\n";
