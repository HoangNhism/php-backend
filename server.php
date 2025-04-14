<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/ChatServer.php';

use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new ChatServer()
        )
    ),
    8080,
    '0.0.0.0'
);

echo "WebSocket server running on port 8080\n";
$server->run();