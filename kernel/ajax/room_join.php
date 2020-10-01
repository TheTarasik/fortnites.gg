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

function protectsqlinj($connect, $value) {
    return mysqli_real_escape_string($connect, $value);
}

$_POST['id_room'] = protectsqlinj($connect, $_POST['id_room']);
$_POST['id'] = protectsqlinj($connect, $_POST['id']);
$_POST['token'] = protectsqlinj($connect, $_POST['token']);

$get_user = mysqli_query($connect, "SELECT * FROM `users` WHERE `id`='" . $_POST['id'] . "'");
while ($get_user_result = mysqli_fetch_assoc($get_user)) {
    $token = $get_user_result['token'];
}

if (isset($_POST['token']) && $_POST['token'] == $token) {
    if (isset($_POST['id_room']) && isset($_POST['id']) && !empty($_POST['id_room']) && !empty($_POST['id'])) {
        $select_isset_data_query = mysqli_query($connect, "SELECT * FROM `joiner` WHERE `id`='" . $_POST['id'] . "' LIMIT 1");
        $select_isset_data = mysqli_num_rows($select_isset_data_query);

        $get_info_room = mysqli_query($connect, "SELECT * FROM `rooms` WHERE `id_room`='" . $_POST['id_room'] . "'");
        while ($get_info_room_result = mysqli_fetch_assoc($get_info_room)) {
            $info_id = $get_info_room_result['id'];
            $info_id_match = $get_info_room_result['id_match'];
        }

        $get_if_isset_room = mysqli_num_rows($get_info_room);

        $get_match = mysqli_query($connect, "SELECT `max_join` FROM `type_match` WHERE `id_match`='" . $info_id_match . "'");
        while ($get_match_info_result = mysqli_fetch_assoc($get_match)) {
            $match_max_join = $get_match_info_result['max_join'];
        }

        $get_joiner_round = mysqli_query($connect, "SELECT * FROM `joiner` WHERE `id_room`='" . $_POST['id_room'] . "'");
        $get_joiner_round_result = mysqli_num_rows($get_joiner_round);


        // Get bet
        $get_bet = mysqli_query($connect, "SELECT `bet` FROM `rooms` WHERE `id_room`='" . $_POST['id_room'] . "'");
        while ($get_bet_result = mysqli_fetch_assoc($get_bet)) {
            $bet = $get_bet_result['bet'];
        }

        // Get user money
        $get_money = mysqli_query($connect, "SELECT `money` FROM `users` WHERE `id`='" . $_POST['id'] . "'");
        while ($get_money_result = mysqli_fetch_assoc($get_money)) {
            $user_money = $get_money_result['money'];
        }

        // Update user money get status
        $money_get_status_update = mysqli_query($connect, "UPDATE `users` SET `money_get_status`='0' WHERE `id`='" . $_POST['id'] . "'");

        if ($user_money < $bet) {
            echo '3';
        } else {
            if ($get_if_isset_room == 0) {
                echo '2';
                exit();
            }

            if ($select_isset_data == 0) {
                $insert_joiner = mysqli_query($connect, "INSERT INTO `joiner` (`id_room`, `id`) VALUES ('" . $_POST['id_room'] . "', '" . $_POST['id'] . "')");
                echo '0';
            } else {
                if ($match_max_join < $get_joiner_round_result) {
                    $delete_if_isset = mysqli_query($connect, "DELETE FROM `joiner` WHERE `id`='" . $_POST['id'] . "'");
                    echo '1';
                    exit();
                }
            }
        }
    }
} // Deny user if him get secret token another user and join the room
?>

