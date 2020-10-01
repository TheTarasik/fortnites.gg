<?php
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest' || strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) < 1) die ();

function reset_url($url) {
    $value = str_replace ( "http://", "", $url );
    $value = str_replace ( "https://", "", $value );
    $value = str_replace ( "www.", "", $value );
    $value = explode ( "/", $value );
    $value = reset ( $value );
    return $value;
}
$_SERVER['HTTP_REFERER'] = reset_url ( $_SERVER['HTTP_REFERER'] );
$_SERVER['HTTP_HOST'] = reset_url ( $_SERVER['HTTP_HOST'] );

if ($_SERVER['HTTP_HOST'] != $_SERVER['HTTP_REFERER']) {
    header('Location: /');
    die();
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/kernel/data/dbconfig.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/kernel/data/config.php');

function protectsqlinj($connect, $value) {
    return mysqli_real_escape_string($connect, $value);
}

$_POST['room_id'] = protectsqlinj($connect, $_POST['room_id']);
$_POST['id'] = protectsqlinj($connect, $_POST['id']);
$_POST['token'] = protectsqlinj($connect, $_POST['token']);

if (isset($_POST['stats']) && !empty($_POST['stats'])) {

    $get_user = mysqli_query($connect, "SELECT * FROM `users` WHERE `id`='" . $_POST['id'] . "'");
    while ($get_user_result = mysqli_fetch_assoc($get_user)) {
        $login = $get_user_result['login'];
        $token = $get_user_result['token'];
        $user_money = $get_user_result['money'];
        $money_get_status = $get_user_result['money_get_status'];
    }

    setcookie("token", $token, time()+60*60*24, '/');

    $get_users = mysqli_query($connect, "SELECT * FROM `users`");

    $get_room = mysqli_query($connect, "SELECT * FROM `rooms` WHERE `id_room`='" . $_POST['room_id'] . "'");
    $get_rooms = mysqli_query($connect, "SELECT * FROM `rooms`");

    $get_joiner = mysqli_query($connect, "SELECT * FROM `joiner` WHERE `id`='" . $_POST['id'] . "'");
    $get_joiners = mysqli_query($connect, "SELECT * FROM `joiner` WHERE `id_room`='" . $_POST['room_id'] . "'");

    // Get isset in room
    $get_isset_in_room_query = mysqli_query($connect, "SELECT `id` FROM `joiner` WHERE `id_room`='" . $_POST['room_id'] . "'");
    $get_isset_in_room_result = mysqli_num_rows($get_isset_in_room_query);

    // Get ready status
    $get_ready_status_query = mysqli_query($connect, "SELECT `ready` FROM `joiner` WHERE `ready` NOT LIKE '0' AND `id_room`='" . $_POST['room_id'] . "'");
    $get_ready_status = mysqli_num_rows($get_ready_status_query);

    // Mini timer render
    $all_ready = false;
    if ($get_ready_status == 2) {
        $all_ready = true;
    }

    $date = strtotime(date("Y-m-d H:i:s"));
    $date_plus = $date + $mini_countdown;

    $get_isset_time_left_query = mysqli_query($connect, "SELECT `date_accept` FROM `rooms` WHERE `id_room`='" . $_POST['room_id'] . "'");
    while ($get_isset_time_left_result = mysqli_fetch_assoc($get_isset_time_left_query)) {
        $get_isset_time_left_date = $get_isset_time_left_result['date_accept'];
    }

    // Join user render
    ob_start();
    echo '<table style="width: 100%;">';
    echo '<tr>
                                    <th>фото</th>
                                    <th>ник real</th>
                                    <th>ник fortnite</th>
                                    <th>статус</th>
                                </tr>';

    while ($get_rooms_info = mysqli_fetch_assoc($get_joiners)) {
        $id_room = $get_rooms_info['id_room'];
        $id = $get_rooms_info['id'];
        $pay_status = $get_rooms_info['ready'];
        $get_login_by_id = mysqli_query($connect, "SELECT * FROM `users` WHERE `id`='" . $id . "'");
        while ($get_login_by_id_result = mysqli_fetch_assoc($get_login_by_id)) {
            $login = $get_login_by_id_result['login'];
            $fortnite_name = $get_login_by_id_result['fortnite_name'];
        }

        if ($pay_status == 0) {
            $pay_status = "<span style='color: red;'>Не готов</span>";
        } else {
            $pay_status = "<span style='color: lime'>Готов</span>";
        }

        echo '<tr> 
            <td> <img class="user-render__userpick" src="/uploads/';
        $photo = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $login . '.jpg';
        if (file_exists($photo)) {
            echo $login . '.jpg';
        } else {
            echo 'default' . '.png';
        }
        echo '"
                            />
            </td>
            <td>' . $login . '</td>
            <th>' . $fortnite_name . '</th>
            <th>' . $pay_status . '</th>
         </tr>
         
         ';
    }
    echo '</table>';

        $joiner_render = json_encode(ob_get_contents());
        ob_end_clean();

        // Get info does user exist in joiner
        $get_joiner_num = mysqli_num_rows($get_joiner);
        if ($get_joiner_num == 1) {
            $active = 1;
        } else {
            $active = 0;
        }

        // Get ready status
        $get_joiners_ready_status = mysqli_num_rows($get_joiners);
        if ($get_joiners_ready_status == 2) {
            $ready = 1;
        } else {
            $ready = 0;
        }

    // Get info if room exist
    $get_room_exist = mysqli_num_rows($get_room);

    // Get creator of room
    $get_creator_of_room_query = mysqli_query($connect, "SELECT `id` FROM `rooms` WHERE `id_room`='" . $_POST['room_id'] . "'");
    while ($get_creator_of_room_result = mysqli_fetch_assoc($get_creator_of_room_query)) {
        $get_creator_of_room = $get_creator_of_room_result['id'];
    }

    // Get creator ready status
    $get_creator_ready_status_query = mysqli_query($connect, "SELECT `ready` FROM `joiner` WHERE `id`='" . $get_creator_of_room . "'");
    while ($get_creator_ready_status_result = mysqli_fetch_assoc($get_creator_ready_status_query)) {
        $get_creator_ready_status = $get_creator_ready_status_result['ready'];
    }

    // Get bet
    $get_bet = mysqli_query($connect, "SELECT `bet` FROM `rooms` WHERE `id_room`='" . $_POST['room_id'] . "'");
    while ($get_bet_result = mysqli_fetch_assoc($get_bet)) {
        $bet = $get_bet_result['bet'];
    }

    // Get user who ready
    $get_user_who_ready_query = mysqli_query($connect, "SELECT `id` FROM `joiner` WHERE `ready`='1' AND `id_room`='" . $_POST['room_id'] . "'");
    while ($get_user_who_ready_result = mysqli_fetch_assoc($get_user_who_ready_query)) {
        $get_user_who_ready = $get_user_who_ready_result['id'];
    }

    if ($_POST['token'] == $token) {

        if ($all_ready == false) {
            $status = 'run';
            if ($get_isset_time_left_date == NULL) {
                if ($get_isset_in_room_result == 2) {
                    $insert_time_left_ready = mysqli_query($connect, "UPDATE `rooms` SET `date_accept`='" . $date_plus . "' WHERE `id_room`='" . $_POST['room_id'] . "'");
                    $status = 'run';
                }
            } else {

                if ($get_isset_in_room_result == 1) {
                    $status = 'stop';
                }

                if ($status == 'stop') {
                    if ($get_isset_in_room_result == 1) {
                        $insert_time_left_ready = mysqli_query($connect, "UPDATE `rooms` SET `date_accept`='" . $date_plus . "' WHERE `id_room`='" . $_POST['room_id'] . "'");
                    }
                }

                while ($get_room_result = mysqli_fetch_assoc($get_room)) {
                    $date_new = $get_room_result['date_accept'];
                }

                $new_time_in_second = $date_new - $date;
                $to_time_new_date_mini_countdown = date("i:s", $new_time_in_second);
            }

            if ($new_time_in_second < 0) {
                $status = "kick";

                // Kick user
                if ($ready == 1) {
                        if ($get_creator_ready_status == 0) {
                            $delete_room = mysqli_query($connect, "DELETE FROM `rooms` WHERE `id`='" . $get_creator_of_room . "'");
                            $user_left_query = mysqli_query($connect, "DELETE FROM `joiner` WHERE `id_room`='" . $_POST['room_id'] . "'");

                            $wait_balance = $user_money + $bet; // Generate new balance
                            if ($money_get_status == 0) {
                                $money_get_status_update = mysqli_query($connect, "UPDATE `users` SET `money_get_status`='1' WHERE `id`='" . $get_user_who_ready . "'");
                                $return_balance = mysqli_query($connect, "UPDATE `users` SET `money`='" . $wait_balance . "' WHERE `id`='" . $get_user_who_ready . "'");
                            }

                        } else {
                            $user_left_query = mysqli_query($connect, "DELETE FROM `joiner` WHERE `ready`='0' AND `id_room`='" . $_POST['room_id'] ."'");
                        }

                }
            }

        } // all ready false

        if ($all_ready == true) {
            $ready_status = 'Ok';
        }


        $data = array (
            "token" => $token,
            "active" => $active,
            "ready" => $ready,
            "joiner_render" => $joiner_render,
            "mini_countdown" => $to_time_new_date_mini_countdown,
            "status" => $status,
            "ready_status" => $ready_status,
            // delete
            "user_money" => $user_money,
            "bet" => $bet,
            // delete
            "test" => $new_time_in_second
        );

        // Adds the interval to delete the room and kick all player
        $delete_room_interval = mysqli_query($connect, "DELETE FROM `rooms` WHERE `date` < (NOW() - INTERVAL '" . $auto_delete_room . "' SECOND)");
        // Checks if room exist, else delete all player from here
        if ($get_room_exist == 0) {
            $delete_joiners_interval = mysqli_query($connect, "DELETE FROM `joiner` WHERE `id_room`='" . $_POST['room_id'] . "'");
        }

        // Send data
        echo json_encode($data);
        } // Deny user get secret token another user
}
?>
