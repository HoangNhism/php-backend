<?php
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Chat.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class ChatServer implements MessageComponentInterface
{
    protected $clients;
    protected $db;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        $database = new Database();
        $this->db = $database->getConnection();
        echo "Chat Server initialized\n";
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        try {
            $data = json_decode($msg, true);
            error_log("Received message data: " . json_encode($data));

            // Ensure the message has a type
            if (!isset($data['type'])) {
                error_log("Invalid message type: Missing 'type' field");
                $from->send(json_encode([
                    'type' => 'error',
                    'message' => "Invalid message type: Missing 'type' field"
                ]));
                return;
            }

            switch ($data['type']) {
                case 'join':
                    // Handle "join" type
                    if (empty($data['userId'])) {
                        error_log("Missing userId in join message");
                        $from->send(json_encode([
                            'type' => 'error',
                            'message' => "Missing userId in join message"
                        ]));
                        return;
                    }

                    // Store the userId with the connection
                    $from->userId = $data['userId'];
                    error_log("User joined with ID: " . $data['userId']);
                    $from->send(json_encode([
                        'type' => 'success',
                        'message' => "User joined successfully"
                    ]));
                    break;

                case 'message':
                    // Handle "message" type
                    if (!isset($data['sender_id'], $data['receiver_id'], $data['message'])) {
                        error_log("Missing required chat fields");
                        $from->send(json_encode([
                            'type' => 'error',
                            'message' => "Missing required chat fields"
                        ]));
                        return;
                    }

                    // Process the message (e.g., save to database, broadcast to clients)
                    $chat = new Chat($this->db);
                    $chat->sender_id = $data['sender_id'];
                    $chat->receiver_id = $data['receiver_id'];
                    $chat->message = $data['message'];
                    $chat->created_at = $data['created_at'] ?? date('Y-m-d H:i:s');

                    if ($chat->create()) {
                        error_log("Message saved successfully with ID: " . $chat->id);

                        // Broadcast the message to all clients
                        foreach ($this->clients as $client) {
                            $client->send(json_encode([
                                'type' => 'message',
                                'id' => $chat->id,
                                'sender_id' => $chat->sender_id,
                                'receiver_id' => $chat->receiver_id,
                                'message' => $chat->message,
                                'created_at' => $chat->created_at
                            ]));
                        }
                    } else {
                        error_log("Failed to save message to database");
                        $from->send(json_encode([
                            'type' => 'error',
                            'message' => "Failed to save message"
                        ]));
                    }
                    break;

                default:
                    error_log("Unhandled message type: " . $data['type']);
                    $from->send(json_encode([
                        'type' => 'error',
                        'message' => "Unhandled message type: " . $data['type']
                    ]));
                    break;
            }
        } catch (Exception $e) {
            error_log("Error in onMessage: " . $e->getMessage());
            $from->send(json_encode([
                'type' => 'error',
                'message' => "Server error: " . $e->getMessage()
            ]));
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}
