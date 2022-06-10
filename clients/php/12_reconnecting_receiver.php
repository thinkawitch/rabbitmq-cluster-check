<?php
require_once '00_common.php';
require_once '00_proto_reconnecting_receiver.php';

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Connection\AMQPStreamConnection;

$log = function(...$args) {
    echo implode(' ', $args), "\n";
};

$onMessage = function(AMQPMessage $message) {
    // Do whatever is needed
};

$connect = function(): AMQPStreamConnection {
    [$host, $port, $user, $pass] = getRMQHostPortUserPass();
    $connection = new AMQPStreamConnection($host, $port, $user, $pass);
    return $connection;
};

$createChannel = function(AMQPStreamConnection $connection) use ($onMessage): AMQPChannel {
    $channel = $connection->channel();
    $channel->queue_declare('flood_queue', false, false, false, false);
    $channel->basic_consume('flood_queue', '', false, true, false, false, $onMessage);
    return $channel;
};

$runLoop = function(AMQPStreamConnection $connection, AMQPChannel $channel): void {
    while ($channel->is_open()) {
        $channel->wait();
    }
};

$log(' [*] Receiver started. To exit press CTRL+C');

rmqReconnectingReceiver(
    $connect,
    $createChannel,
    $runLoop,
    $log
);

$log(' [x] Stop ');
