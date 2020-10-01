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

        if (!function_exists('protectsqlinj')) {
            function protectsqlinj($connect, $value) {
                return mysqli_real_escape_string($connect, $value);
            }
        }

            $data['user_id'] = protectsqlinj($connect, $data['user_id']);
            $data['room_id'] = protectsqlinj($connect, $data['room_id']);

        $get_login_by_id = mysqli_query($connect, "SELECT * FROM `users` WHERE `id`='" . $data['user_id'] . "'");

        while ($get_login_by_id_result = mysqli_fetch_assoc($get_login_by_id)) {
            $login = $get_login_by_id_result['login'];
            $token = $get_login_by_id_result['token'];
            $money_get_status = $get_login_by_id_result['money_get_status']; // <---
        }

        $get_info_room = mysqli_query($connect, "SELECT * FROM `rooms` WHERE `id_room`='" . $data['room_id'] . "'");
        while ($get_info_room_result = mysqli_fetch_assoc($get_info_room)) {
            $info_id = $get_info_room_result['id'];
        }

        $get_info_joiner = mysqli_query($connect, "SELECT * FROM `joiner` WHERE `id`='" . $data['user_id'] . "'");
        while ($get_info_joiner_result = mysqli_fetch_assoc($get_info_joiner)) {
            $info_joiner_room_id = $get_info_joiner_result['id_room'];
            $info_status_ready = $get_info_joiner_result['ready'];
        }

        // Get bet
        $get_bet = mysqli_query($connect,"SELECT `bet` FROM `rooms` WHERE `id_room`='" . $data['room_id'] . "'");
        while ($get_bet_result = mysqli_fetch_assoc($get_bet)) {
            $bet = $get_bet_result['bet'];
        }

        // Get user money
        $get_money = mysqli_query($connect, "SELECT `money` FROM `users` WHERE `id`='" . $data['user_id'] . "'");
        while ($get_money_result = mysqli_fetch_assoc($get_money)) {
            $user_money = $get_money_result['money'];
        }

        if (isset($data['token']) && $data['token'] == $token) {
            if (isset($data['action']) && $data['action'] == 'leave') {
                $user_left_query = mysqli_query($connect, "DELETE FROM `joiner` WHERE `id`='" . $data['user_id'] . "'");
                if ($data['user_id'] == $info_id) {
                    $delete_room = mysqli_query($connect, "DELETE FROM `rooms` WHERE `id`='" . $data['user_id'] . "'");
                    $user_left_query = mysqli_query($connect, "DELETE FROM `joiner` WHERE `id_room`='" . $data['room_id'] . "'");
                }
            }

            if (isset($data['action']) && $data['action'] == 'ready_status') {
                if ($user_money < $bet) {
                    if ($info_status_ready == 1) {
                        $status = 2;
                    } else {
                        $status = 0;
                    }
                } else {
                    if ($info_status_ready == 0) {
                        $status = 1;
                        $current_balace = $user_money - $bet;
                        $change_user_money = mysqli_query($connect, "UPDATE `users` SET `money`='" . $current_balace . "' WHERE `id`='" . $data['user_id'] . "'");
                        $change_user_status = mysqli_query($connect, "UPDATE `joiner` SET `ready`='1' WHERE `id`='" . $data['user_id'] . "'");
                    } else {
                        $status = 2;
                    }
                }
            }

            if (isset($data['action']) && $data['action'] == 'xhr_content_blocked') {

                // Get creator of room
                $get_creator_of_room_query = mysqli_query($connect, "SELECT `id` FROM `rooms` WHERE `id_room`='" . $data['room_id'] . "'");
                while ($get_creator_of_room_result = mysqli_fetch_assoc($get_creator_of_room_query)) {
                    $get_creator_of_room = $get_creator_of_room_result['id'];
                }

                // Get creator ready status
                $get_creator_ready_status_query = mysqli_query($connect, "SELECT `ready` FROM `joiner` WHERE `id`='" . $get_creator_of_room . "'");
                while ($get_creator_ready_status_result = mysqli_fetch_assoc($get_creator_ready_status_query)) {
                    $get_creator_ready_status = $get_creator_ready_status_result['ready'];
                }

                // Get user who ready
                $get_user_who_ready_query = mysqli_query($connect, "SELECT `id` FROM `joiner` WHERE `ready`='1' AND `id_room`='" . $data['room_id'] . "'");
                while ($get_user_who_ready_result = mysqli_fetch_assoc($get_user_who_ready_query)) {
                    $get_user_who_ready = $get_user_who_ready_result['id'];
                }


                // Get enemy from joiner
                $get_enemy_query = mysqli_query($connect, "SELECT * FROM `joiner` WHERE `id` NOT LIKE '" . $get_creator_of_room . "' AND `id_room`='" . $data['room_id'] ."'");
                while ($get_enemy_result = mysqli_fetch_assoc($get_enemy_query)) {
                    $get_enemy = $get_enemy_result['id'];
                    $get_enemy_ready_status = $get_enemy_result['ready'];
                }

                // Get enemy from users
                $get_enemy_from_users_query = mysqli_query($connect, "SELECT * FROM `users` WHERE `id`='" . $get_enemy . "'");
                while ($get_enemy_from_users_result = mysqli_fetch_assoc($get_enemy_from_users_query)) {
                    $get_enemy_money = $get_enemy_from_users_result['money'];
                }

                // Money get status enemy
                $money_get_status_enemy_query = mysqli_query($connect, "SELECT `money_get_status` FROM `users` WHERE `id`='" . $get_enemy . "'");
                while ($money_get_status_enemy_result = mysqli_fetch_assoc($money_get_status_enemy_query)) {
                    $money_get_status_enemy = $money_get_status_enemy_result['money_get_status'];
                }

                // Get all ready status
                $get_all_ready_status_query = mysqli_query($connect, "SELECT `ready` FROM `joiner` WHERE `ready` NOT LIKE '0' AND `id_room`='" . $data['room_id'] . "'");
                $get_all_ready_status = mysqli_num_rows($get_all_ready_status_query);

                if ($get_all_ready_status != 2) {

                    if ($data['user_id'] == $get_creator_of_room) {
                        // Creator
                        if ($get_creator_ready_status == 1) {
                            $wait_balance = $user_money + $bet; // Generate new balance
                            if ($money_get_status == 0) {
                                $return_balance = mysqli_query($connect, "UPDATE `users` SET `money`='" . $wait_balance . "' WHERE `id`='" . $get_creator_of_room . "'");
                                $money_get_status_update = mysqli_query($connect, "UPDATE `users` SET `money_get_status`='1' WHERE `id`='" . $get_creator_of_room . "'");
                            }
                        }

                        if ($get_enemy_ready_status == 1) {
                            $enemy_wait_balance = $get_enemy_money + $bet; // Generate new balance
                            if ($money_get_status_enemy == 0) {
                                $money_get_status_update = mysqli_query($connect, "UPDATE `users` SET `money_get_status`='1' WHERE `id`='" . $get_enemy . "'");
                                $return_balance = mysqli_query($connect, "UPDATE `users` SET `money`='" . $enemy_wait_balance . "' WHERE `id`='" . $get_enemy . "'");
                            }
                        }

                        $delete_room = mysqli_query($connect, "DELETE FROM `rooms` WHERE `id`='" . $get_creator_of_room . "'");
                        $user_left_query = mysqli_query($connect, "DELETE FROM `joiner` WHERE `id_room`='" . $data['room_id'] . "'");

                    }


                    if ($data['user_id'] == $get_enemy) {
                        // Not creator
                        if ($get_enemy_ready_status == 1) {
                            $enemy_wait_balance = $get_enemy_money + $bet; // Generate new balance
                            if ($money_get_status_enemy == 0) {
                                $money_get_status_update = mysqli_query($connect, "UPDATE `users` SET `money_get_status`='1' WHERE `id`='" . $get_enemy . "'");
                                $return_balance = mysqli_query($connect, "UPDATE `users` SET `money`='" . $enemy_wait_balance . "' WHERE `id`='" . $get_enemy . "'");
                            }
                        }

                        $user_left_query = mysqli_query($connect, "DELETE FROM `joiner` WHERE `id`='" . $get_enemy . "'");

                    }

                } else {

                    if ($get_all_ready_status == 2) {
                        if ($data['user_id'] == $get_creator_of_room) {
                            // Creator

                            if ($get_enemy_ready_status == 1) {
                                $enemy_wait_balance = $get_enemy_money + $bet; // Generate new balance
                                if ($money_get_status_enemy == 0) {
                                    $money_get_status_update = mysqli_query($connect, "UPDATE `users` SET `money_get_status`='1' WHERE `id`='" . $get_enemy . "'");
                                    $return_balance = mysqli_query($connect, "UPDATE `users` SET `money`='" . $enemy_wait_balance . "' WHERE `id`='" . $get_enemy . "'");
                                }
                            }

                            $delete_room = mysqli_query($connect, "DELETE FROM `rooms` WHERE `id`='" . $get_creator_of_room . "'");
                            $user_left_query = mysqli_query($connect, "DELETE FROM `joiner` WHERE `id_room`='" . $data['room_id'] . "'");

                        }

                        if ($data['user_id'] == $get_enemy) {
                            // Not creator
                            $user_left_query = mysqli_query($connect, "DELETE FROM `joiner` WHERE `id`='" . $get_enemy . "'");
                        }
                    }
                } // get all ready status == 2
            } // xhr_content_blocked
        }

        $token_generate = md5(md5(rand(1000,9999).rand(1000,9999).rand(1000,9999)));

        $chat_sender = array (
            "room_id" => $data['room_id'],
            "login" => $login,
            "action" => $data['action'],
            "msg" => $data['msg'],
            "token" => $data['token']
        );


        $leave = array (
            "room_id" => $data['room_id'],
            "user_id" => $data['user_id'],
            "action" => $data['action'],
            "token" => $data['token']
        );

        $ready_status = array (
            "room_id" => $data['room_id'],
            "user_id" => $data['user_id'],
            "action" => $data['action'],
            "token" => $data['token'],
            "status" => $status,
            "current_balance" => $current_balace
        );

        $xhr_content_blocked = array (
            "room_id" => $data['room_id'],
            "user_id" => $data['user_id'],
            "action" => $data['action'],
            "token" => $data['token'],
            "test" => $get_all_ready_status
        );

//        $other = array(
//            "login" => $login,
//            "action" => $data['action']
//        );

       // $insert_joiner = mysqli_query($connect, "INSERT INTO `log` (`name`, `ip`) VALUES ('" . $data['user_id'] . "', '" . $data['room_id'] . "')");

        foreach ($this->clients as $client) {
            //if ($from !== $client) {
            // $client->send(json_encode($other));
            // $client->send($msg);
            //}

            if (isset($data['token']) && $data['token'] == $token) {
                if ($data['room_id'] == $info_joiner_room_id) {
                    if (isset($data['action'])) {
                        if ($data['action'] == 'chat_sender') {
                            if (trim($data['msg'], ' ') != '') {
                                $client->send(json_encode($chat_sender));
                            }
                        }

                        if ($data['action'] == 'leave') {
                            $client->send(json_encode($leave));
                        }

                        if ($data['action'] == 'ready_status') {
                            $client->send(json_encode($ready_status));
                        }

                        if ($data['action'] == 'xhr_content_blocked') {
                            $client->send(json_encode($xhr_content_blocked));
                        }
                    }
                    $new_token = mysqli_query($connect, "UPDATE `users` SET `token`='" . $token_generate  . "' WHERE `id`= '" . $data['user_id'] . "'");
                } // If user send message in another room - this returns him.
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