<?php
require_once '00_common.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

[$host, $user, $pass] = getHostUserPass();

$connection = new AMQPStreamConnection($host, 5672, $user, $pass);
$channel = $connection->channel();

$channel->queue_declare('flood_queue', false, false, false, false);

echo " [*] Waiting for messages. To exit press CTRL+C\n";

$total = 0;
$echoLimit = 500;
$echoCounter = 0;

$callback = function ($msg) use (&$total, $echoLimit, &$echoCounter) {
    $total++;
    $echoCounter++;
    //echo ' [x] Received ', $msg->body, "\n";
    if ($echoCounter >= $echoLimit) {
        $echoCounter = 0;
        echo ' [x] Received ', $total, "\n";
    }
};

$channel->basic_consume('flood_queue', '', false, true, false, false, $callback);

while ($channel->is_open()) {
    $channel->wait();
}

$channel->close();
$connection->close();
