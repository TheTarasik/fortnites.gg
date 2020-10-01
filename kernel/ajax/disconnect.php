<?php
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest' || strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) < 1) die ();
require_once($_SERVER['DOCUMENT_ROOT'] . '/kernel/data/dbconfig.php');

function protectsqlinj($connect, $value) {
    return mysqli_real_escape_string($connect, $value);
}

$token = protectsqlinj($connect, $_COOKIE['token']);

$get_user = mysqli_query($connect, "SELECT * FROM `users` WHERE `token`='" . $token  . "'");
while ($get_user_result = mysqli_fetch_assoc($get_user)) {
    $id = $get_user_result['id'];
    $login = $get_user_result['login'];
}

$get_joiner_info = mysqli_query($connect, "SELECT * FROM `joiner` WHERE `id`='" . $id . "'");
while ($get_joiner_info_result = mysqli_fetch_assoc($get_joiner_info)) {
    $id_room = $get_joiner_info_result['id_room'];
}

$get_info_room = mysqli_query($connect, "SELECT * FROM `rooms` WHERE `id_room`='" . $id_room . "'");
while ($get_info_room_result = mysqli_fetch_assoc($get_info_room)) {
    $info_id = $get_info_room_result['id'];
    $info_id_match = $get_info_room_result['id_match'];
}

$user_left_query = mysqli_query($connect, "DELETE FROM `joiner` WHERE `id`='" . $id . "'");
if ($id == $info_id) {
    $delete_room = mysqli_query($connect, "DELETE FROM `rooms` WHERE `id`='" . $id . "'");
    $user_left_query = mysqli_query($connect, "DELETE FROM `joiner` WHERE `id_room`='" . $id_room . "'");
}
?>

