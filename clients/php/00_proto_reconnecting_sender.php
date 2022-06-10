<?php
use PhpAmqpLib\Exception\AMQPChannelClosedException;
use PhpAmqpLib\Exception\AMQPConnectionClosedException;

// base on https://github.com/php-amqplib/php-amqplib/issues/444

function rmqReconnectingSender(
    callable $connect,
    callable $createChannel,
    callable $runLoop,
    callable $log,
    int $connectionAttemptsLimit = 30
): void {

    $connectionRequired = true;
    $connectionAttempts = 0;

    $channel = null;
    $onCtrlC = function() use (&$channel, &$connectionRequired, $log): void {
        $connectionRequired = false;
        $channel && $channel->close();
    };
    pcntl_signal(SIGINT, $onCtrlC);

    try {
        // This outer loop enabled the script to try to reconnect in case of failure
        while ($connectionRequired && $connectionAttempts < $connectionAttemptsLimit) {
            $connectionAttempts++;
            $foundQueue = false;
            try {
                // Attempt Connection
                $connection = $connect();
                $foundQueue = false;

                try {
                    $sp = $connection->getServerProperties();
                    $clusterName = $sp['cluster_name'][1];
                    $log(" [*] Connected to $clusterName");

                    $channel = $createChannel($connection);

                    // We're good!
                    $foundQueue = true;
                    // Reset the connection attempt counter
                    $connectionAttempts = 0;
                } catch (AMQPConnectionClosedException $e) {
                    sleep(1);
                    $log(" [!] try to reconnect #$connectionAttempts in createChannel");
                } catch (\Exception $e) {
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

            } catch (AMQPConnectionClosedException $e) {
                sleep(1);
                $log(" [!] try to reconnect #$connectionAttempts in connect");
            } catch (\Exception $e) {
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
                    $runLoop($connection, $channel);
                    $channel->close();
                    $connection->close();
                    // all done
                    $connectionRequired = false;
                } catch (AMQPChannelClosedException $e) {
                    if ($connectionRequired) {
                        throw $e;
                    } else {
                        // do nothing, should be ctrl+c pressed
                    }
                } catch (AMQPConnectionClosedException $e) {
                    // do nothing
                    $log(' [!] disconnected');
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
                    throw $e;
                }
            }
        }

    } catch (\Exception $e) {
        // You'll end up here if something's gone wrong
        throw $e;
    }
}



