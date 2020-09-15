<?php
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest' || strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) < 1) die ();
require_once($_SERVER['DOCUMENT_ROOT'] . '/kernel/data/dbconfig.php');

if (isset($_POST['stats']) && !empty($_POST['stats'])) {
    $get_user = mysqli_query($connect, "SELECT * FROM `users` WHERE `id`='" . $_POST['id'] . "'");
    while ($get_user_result = mysqli_fetch_assoc($get_user)) {
        $token = $get_user_result['token'];
    }

    $get_users = mysqli_query($connect, "SELECT * FROM `rooms`");

    $get_room = mysqli_query($connect, "SELECT * FROM `rooms` WHERE `id_room`='" . $_POST['room_id'] . "'");
    $get_rooms = mysqli_query($connect, "SELECT * FROM `rooms`");

    $get_joiner = mysqli_query($connect, "SELECT * FROM `joiner` WHERE `id`='" . $_POST['id'] . "'");
    // Check if user locate in room
    $get_joiner_num = mysqli_num_rows($get_joiner);
    if ($get_joiner_num == 1) {
        $active = 1;
    }  else {
        $active = 0;
    }

    $data = array(
        "token" => $token,
        "active" => $active
    );

    echo json_encode($data);
}
?>
