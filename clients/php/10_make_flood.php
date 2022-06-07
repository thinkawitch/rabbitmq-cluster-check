<?php

require_once '00_common.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

[$host, $user, $pass] = getHostUserPass();

$connection = new AMQPStreamConnection($host, 5672, $user, $pass);
$channel = $connection->channel();

$channel->queue_declare('flood_queue', false, false, false, false);

$mtStart = microtime(true);

$limit = 1_000_000;
for ($i=1; $i<=$limit; $i++) {
    $msg = new AMQPMessage(uniqid('message-', true));
    $channel->basic_publish($msg, '', 'flood_queue');
}

$mtEnd = microtime(true);
$seconds = $mtEnd - $mtStart;

echo " [x] Sent $limit messages in {$seconds} seconds\n";

$channel->close();
$connection->close();

