<?php
require 'vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

        $host = "127.0.0.1";
        $database = "fortnite";
        $user = "mysql";
        $password = "mysql";

        $connect = mysqli_connect($host, $user, $password, $database)
        or die("Connection to database didn't established!" .mysqli_error() );

        $data = json_decode($msg, true);

        $get_login_by_id = mysqli_query($connect, "SELECT * FROM `users` WHERE `id`='" . $data['user_id'] . "'");

        while ($get_login_by_id_result = mysqli_fetch_assoc($get_login_by_id)) {
            $login = $get_login_by_id_result['login'];
        }

        $get_info_room = mysqli_query($connect, "SELECT * FROM `rooms` WHERE `id_room`='" . $data['room_id'] . "'");
        while ($get_info_room_result = mysqli_fetch_assoc($get_info_room)) {
            $info_id = $get_info_room_result['id'];
        }

        if(isset($data['action']) && $data['action'] == 'leave') {
            $user_left_query = mysqli_query($connect, "DELETE FROM `joiner` WHERE `id`='" . $data['user_id'] . "'");

            if ($data['user_id'] == $info_id) {
                $delete_room = mysqli_query($connect, "DELETE FROM `rooms` WHERE `id`='" . $data['user_id'] . "'");
                $user_left_query = mysqli_query($connect, "DELETE FROM `joiner` WHERE `id_room`='" . $data['room_id'] . "'");
            }

        }

        $chat_sender = array(
            "room_id" => $data['room_id'],
            "login" => $login,
            "action" => $data['action'],
            "msg" => $data['msg']
        );


        $leave = array(
            "room_id" => $data['room_id'],
            "user_id" => $data['user_id'],
            "action" => $data['action']
        );

//        $other = array(
//            "login" => $login,
//            "action" => $data['action']
//        );

       // $insert_joiner = mysqli_query($connect, "INSERT INTO `log` (`name`, `ip`) VALUES ('" . $data['user_id'] . "', '" . $data['room_id'] . "')");

        foreach ($this->clients as $client) {
            //if ($from !== $client) {
           // $client->send(json_encode($other));
            if (isset($data['action'])) {
                if ($data['action'] == 'chat_sender') {
                    $client->send(json_encode($chat_sender));
                }

                if ($data['action'] == 'leave') {
                    $client->send(json_encode($leave));
                }
            }
         //  $client->send($msg);

            //}
        }
    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}


$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Chat()
        )
    ),
    4433
);

$server->run();