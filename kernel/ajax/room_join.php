<?php
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest' || strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) < 1) die ();
require_once($_SERVER['DOCUMENT_ROOT'] . '/kernel/data/dbconfig.php');

if(isset($_POST['id_room']) && isset($_POST['id']) && !empty($_POST['id_room']) && !empty($_POST['id'])) {
    $select_isset_data_query = mysqli_query($connect, "SELECT * FROM `joiner` WHERE `id`='" . $_POST['id'] . "' LIMIT 1");
    $select_isset_data = mysqli_num_rows($select_isset_data_query);

    $get_info_room = mysqli_query($connect, "SELECT * FROM `rooms` WHERE `id_room`='" . $_POST['id_room'] . "'");
    while ($get_info_room_result = mysqli_fetch_assoc($get_info_room)) {
        $info_id = $get_info_room_result['id'];
        $info_id_match = $get_info_room_result['id_match'];
    }

    $get_match = mysqli_query($connect, "SELECT `max_join` FROM `type_match` WHERE `id_match`='" . $info_id_match . "'");
    while ($get_match_info_result = mysqli_fetch_assoc($get_match)) {
        $match_max_join = $get_match_info_result['max_join'];
    }

    $get_joiner_round = mysqli_query($connect, "SELECT * FROM `joiner` WHERE `id_room`='" . $_POST['id_room'] . "'");
    $get_joiner_round_result = mysqli_num_rows($get_joiner_round);

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
?>

