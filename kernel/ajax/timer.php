<?php
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest' || strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) < 1) die ();
require_once($_SERVER['DOCUMENT_ROOT'] . '/kernel/data/dbconfig.php');

function protectsqlinj($connect, $value) {
    return mysqli_real_escape_string($connect, $value);
}

$user_id = protectsqlinj($connect, $_POST['id']);
$room_id = protectsqlinj($connect, $_POST['room_id']);
if (isset($_POST['timer']) && !empty($_POST['timer'])) {
    $token_generate = md5(md5(rand(1000,9999).rand(1000,9999).rand(1000,9999)));

    // Get creator of room
    $get_creator_id_from_room_query = mysqli_query($connect, "SELECT `id` FROM `rooms` WHERE `id_room`='" . $room_id . "'");
    while ($get_creator_id_from_room_result = mysqli_fetch_assoc($get_creator_id_from_room_query)) {
        $get_creator_id_from_room = $get_creator_id_from_room_result['id'];
    }

    // Get id not creator from rooms
    $get_creator = mysqli_query($connect, "SELECT `id` FROM `rooms` WHERE `id`='" . $user_id . "'");
    while ($get_creator_result = mysqli_fetch_assoc($get_creator)) {
        $get_creator_id = $get_creator_result['id'];
    }

    // Get token creator
    $get_creator_left_token_query = mysqli_query($connect, "SELECT `left_token` FROM `joiner` WHERE `id`='" . $get_creator_id_from_room  . "'");
    while ($get_creator_left_token_result = mysqli_fetch_assoc($get_creator_left_token_query)) {
        $get_creator_left_token = $get_creator_left_token_result['left_token'];
    }

    // Get token not creator
    $get_token_in_not_creator_query = mysqli_query($connect, "SELECT `left_token` FROM `joiner` WHERE `id`='" . $user_id  . "'");
    while ($get_token_in_not_creator_result = mysqli_fetch_assoc($get_token_in_not_creator_query)) {
        $get_token_in_not_creator = $get_token_in_not_creator_result['left_token'];
    }

    // Comparison data from post query and mysql query
    if ($get_creator_id == $user_id) {
       // echo 'You creator';
        $insert_left_token = mysqli_query($connect, "UPDATE `joiner` SET `left_token`='" . $token_generate . "' WHERE `id_room`='" . $room_id . "'");
    } else {
        //  echo 'You not creator';
        $join_check = false;
        if ($get_token_in_not_creator == $get_creator_left_token) {
            $insert_left_token_not_creator = mysqli_query($connect, "UPDATE `joiner` SET `left_token`='" . $token_generate . "' WHERE `id`='" . $user_id . "'");
            $get_origin_token_query = mysqli_query($connect, "SELECT `left_token` FROM `joiner` WHERE `id`='" . $user_id . "'");
            while ($get_origin_token_result = mysqli_fetch_assoc($get_origin_token_query)) {
                $get_origin_token = $get_origin_token_result['left_token'];
            }

            if ($get_origin_token != $get_creator_left_token) {
                $join_check = true;
            }
        }

        if ($join_check == false) {
            echo 'left';
        } else {
            echo 'join';
        }

    }
}

?>