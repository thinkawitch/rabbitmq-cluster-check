<?php
require_once '00_common.php';
require_once '00_proto_reconnecting_sender.php';

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Connection\AMQPStreamConnection;

$mtStart = microtime(true);

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
    return $channel;
};
declare(ticks=1); // for better keyboard handling
$limit = 1_000_000;
$limit = 50_000;
$sent = 0;
$runLoop = function(AMQPStreamConnection $connection, AMQPChannel $channel) use ($limit, &$sent, $log): void {
    for ($i=$sent; $i<$limit; $i++) {
        $msg = new AMQPMessage(uniqid('message-', true));
        $channel->basic_publish($msg, '', 'flood_queue');
        $sent++;
        usleep(10); // give time for keyboard
    }
};

$log(' [*] Sender started. To exit press CTRL+C');

rmqReconnectingSender(
    $connect,
    $createChannel,
    $runLoop,
    $log
);

$mtEnd = microtime(true);
$seconds = $mtEnd - $mtStart;

$log(" [x] Sent $limit messages in $seconds seconds");
$log(' [x] Stop ');
