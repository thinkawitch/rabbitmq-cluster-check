<?php
require_once '00_common.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

[$host, $port, $user, $pass] = getRMQHostPortUserPass();

$connection = new AMQPStreamConnection($host, $port, $user, $pass);
$channel = $connection->channel();

$channel->queue_declare('flood_queue', false, false, false, false);

echo " [*] Waiting for messages. To exit press CTRL+C\n";

$total = 0;
$echoLimit = 500;
$echoCounter = 0;

$callback = function ($msg) use (&$total, $echoLimit, &$echoCounter): void {
    $total++;
    $echoCounter++;
    //echo ' [x] Received ', $msg->body, "\n";
    if ($echoCounter >= $echoLimit) {
        $echoCounter = 0;
        echo ' [x] Received ', $total, "\n";
    }
};

$channel->basic_consume('flood_queue', '', false, true, false, false, $callback);

$onCtrlC = function() use ($channel): void {
    echo "\n";
    $channel->close();
};
//declare(ticks = 1);
pcntl_signal(SIGINT, $onCtrlC);

while ($channel->is_open()) {
    $channel->wait();
}

$channel->close();
$connection->close();

echo ' [x] Total received ', $total, "\n";
echo ' [x] Stop ', "\n";
