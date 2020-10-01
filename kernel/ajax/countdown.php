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

if (isset($_POST['countdown']) && !empty($_POST['countdown'])) {

    $get_user = mysqli_query($connect, "SELECT * FROM `users` WHERE `id`='" . $_POST['id'] . "'");
    while ($get_user_result = mysqli_fetch_assoc($get_user)) {
        $login = $get_user_result['login'];
        $token = $get_user_result['token'];
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
                }

            } // all ready false

            if ($all_ready == true) {

            }


        $data = array (
            "mini_countdown" => $to_time_new_date_mini_countdown,
            "status" => $status
        );

        // Send data
        echo json_encode($data);
    } // Deny user get secret token another user
}

?>
