<?php

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Server\IoServer;

require __DIR__ . '/vendor/autoload.php';

class WebSocketServer implements MessageComponentInterface {
    protected $connections = [];

    public function onOpen(ConnectionInterface $conn) {
        $this->connections[] = $conn;
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        foreach ($this->connections as $connection) {
            $connection->send($msg);
        }
        echo $msg . "\n";
    }

    public function onClose(ConnectionInterface $conn) {
        $index = array_search($conn, $this->connections);
        if ($index !== false) {
            array_splice($this->connections, $index, 1);
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new WebSocketServer()
        )
    ),
    9600,
    '127.0.0.1' // Listen only on localhost
);

$server->run();
