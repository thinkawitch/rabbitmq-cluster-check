<?php
require_once '00_common.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPConnectionClosedException;
use PhpAmqpLib\Message\AMQPMessage;

[$host, $port, $user, $pass] = getRMQHostPortUserPass();

echo " [*] Receiver started. To exit press CTRL+C\n";

$connectionRequired = true;
$connectionAttempts = 0;

$connection = null;
$channel = null;

$onMessage = function(AMQPMessage $message) {
    // Do whatever is needed
};

$onCtrlC = function() use (&$channel, &$connectionRequired): void {
    echo "\n";
    $connectionRequired = false;
    $channel->close();
};
//declare(ticks = 1);
pcntl_signal(SIGINT, $onCtrlC);

// https://github.com/php-amqplib/php-amqplib/issues/444
try {
    #$connectionRequired = true;
    #$connectionAttempts = 0;

    // This outer loop enabled the script to try to reconnect in case of failure
    while ($connectionRequired && $connectionAttempts < 30) {
        $connectionAttempts++;
        $foundQueue = false;
        try {
            // Attempt Connection . "\n";
            $connection = new AMQPStreamConnection($host, $port, $user, $pass);
            $foundQueue = false;

            try {
                $sp = $connection->getServerProperties();
                $clusterName = $sp['cluster_name'][1];
                echo " [*] Connected to $clusterName\n";

                $channel = $connection->channel();
                #$channel->basic_qos(null, MAX_MESSAGES_TO_HANDLE, null);
                #$channel->queue_declare('task_queue', false, false, false, false);
                $channel->queue_declare('flood_queue', false, false, false, false);

                $channel->basic_consume('flood_queue', '', false, true, false, false, $onMessage);

                // We're good!
                $foundQueue = true;

                // Reset the connection attempt counter
                $connectionAttempts = 0;

            } catch(\Exception $e) {
                // Failed to get channel
                // Best practice is to catch the specific exceptions and handle accordingly.
                // Either handle the message (and exit) or retry

                /*if (YouWantToRetry) {
                    sleep(5);  // Time should greacefully decrade based on "connectionAttempts"
                } elseif (YouCanHandleTheErrorAndWantToExitGraceully, e.g. $connectionAttempts > threshold OR youKnowTheException) {
                    $connectionRequired = false;
                } elseif (YouCannotHandleTheErrorAndWantToGetOutOfHere) {
                throw ($e);
                }*/
                throw($e);
            }
        } catch(\Exception $e) {
            // Failed to get connection.
            // Best practice is to catch the specific exceptions and handle accordingly.
            // Either handle the message (and exit) or retry

            /*if (YouWantToRetry) {
                sleep(5);  // Time should greacefully decrade based on "connectionAttempts"
            } elseif (YouCanHandleTheErrorAndWantToExitGraceully, e.g. $connectionAttempts > threshold OR youKnowTheException) {
                $connectionRequired = false;
            } elseif (YouCannotHandleTheErrorAndWantToGetOutOfHere) {
            throw ($e);
            }*/
            throw($e);
        }

        if ($foundQueue) {
            try {
                /*while(count($channel->callbacks)) {
                    $channel->wait(null, true, null);
                }*/
                while ($channel->is_open()) {
                    $channel->wait();
                }

                $channel->close();
                $connection->close();

            } catch(\Exception $e) {

                // Consider this carefully!
                // Best practice is to catch the specific exceptions and handle accordingly.

                /*if (YouWantToRetry) {
                    sleep(5);
                    $foundQueue = false;
                } elseif (YouCanHandleTheErrorAndWantToExitGraceully) {
                    $foundQueue = false;
                    $connectionRequired = false;
                } elseif (YouCannotHandleTheErrorAndWantToGetOutOfHere) {
                    // Error, so throw out of here
                    throw $e;
                }*/
            }
        }
    }

    // You'll end here on a graceful exit

} catch (\Exception $e) {
    // You'll end up here if something's gone wrong

}

echo ' [x] Stop ', "\n";
