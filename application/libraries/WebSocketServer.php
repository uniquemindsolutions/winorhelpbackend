<?php 
// application/libraries/WebSocketServer.php
require __DIR__ . '/../../vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory;
use React\Socket\Server as ReactSocketServer;
use Ratchet\Server\IoServer;


class WebSocketServer implements MessageComponentInterface {
    protected $clients;
    private $room;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->initializeRoom();
        
        $this->ci =& get_instance();
        $this->ci->load->model('Game_model');
        $this->gameModel = $this->ci->Game_model;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
        $conn->send(json_encode(['type' => 'INIT', 'room' => $this->room]));
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);

        if ($data['type'] === 'GET_GAME_STATE') {
            $game = $this->gameModel->getGame();
            $from->send(json_encode(['type' => 'GAME_STATE_UPDATE', 'data' => $game]));
        } elseif ($data['type'] === 'UPDATE_GAME_STATE') {
            $this->gameModel->updateGame($data['gameState']);
            $game = $this->gameModel->getGame();
            
            foreach ($this->clients as $client) {
                if ($client !== $from && $client->isOpen()) {
                    $client->send(json_encode(['type' => 'GAME_STATE_UPDATE', 'data' => $game]));
                }
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    private function initializeRoom() {
        $this->room = [
            'id' => 1,
            'name' => 'Room 1',
            'lotteryDate' => time() + 60000, // 1 minute from now
            'roundDuration' => 60000, // 1 minute per round
            'users' => [
                ['id' => 1, 'name' => 'User 1'],
                ['id' => 2, 'name' => 'User 2'],
                ['id' => 3, 'name' => 'User 3'],
                ['id' => 4, 'name' => 'User 4'],
                ['id' => 5, 'name' => 'User 5'],
                ['id' => 6, 'name' => 'User 6']
            ],
            'winners' => [],
            'manualWinners' => [], // List of manual winners
            'currentRound' => 1,
            'timeRemaining' => [],
            'manualSelection' => true // Add manualSelection flag
        ];
    }

    private function startGame() {
        $now = time();
        $this->room['lotteryDate'] = $now + $this->room['roundDuration'];
    }
}

// use Ratchet\Server\IoServer;
// use Ratchet\Http\HttpServer;
// use Ratchet\WebSocket\WsServer;


$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new WebSocketServer()
        )
    ),
    8082
);

$server->run();


?>