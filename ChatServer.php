<?php

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class ChatServer implements MessageComponentInterface {
    private $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);

        switch ($data['type']) {
            case 'join':
                $from->userId = $data['user']['id'];
                echo "User {$data['user']['id']} joined.\n";
                break;

            case 'message':
                foreach ($this->clients as $client) {
                    if ($client !== $from) {
                        $client->send(json_encode([
                            'type' => 'message',
                            'sender_id' => $from->userId,
                            'message' => $data['message']
                        ]));
                    }
                }
                break;
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected.\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}