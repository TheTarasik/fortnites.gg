<?php
session_start();
$title = 'Room';
$id_room = $_REQUEST['id'];
require_once($_SERVER['DOCUMENT_ROOT'] . '/kernel/pages/core/header.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/kernel/data/dbconfig.php');

if (!(isset($_SESSION['logged']))) {
    header("Location: /?page=login");
    exit;
} else {
    $get_user = mysqli_query($connect, "SELECT * FROM `users` WHERE `login`='" . $login  . "'");
    while ($get_user_result = mysqli_fetch_assoc($get_user)) {
        $id = $get_user_result['id'];
        $login = $get_user_result['login'];
        $email = $get_user_result['email'];
        $password = $get_user_result['password'];
        $user_group = $get_user_result['user_group'];
        $ip = $get_user_result['ip'];
    }
}

if ($id_room == '') {
    header('Location: /game/lobby.php');
    exit;
}

if (preg_match('/[^,.0-9]/', $id_room)) {
    header('Location: /game/lobby.php');
    exit;
}

$check_isset_room_query = mysqli_query($connect, "SELECT `id_room` FROM `rooms` WHERE `id_room`='" . $id_room . "'");
$check_isset_room = mysqli_num_rows($check_isset_room_query);

if ($check_isset_room == 0) {
    header('Location: /game/lobby.php');
    exit;
}

$get_info_room = mysqli_query($connect, "SELECT * FROM `rooms` WHERE `id_room`='" . $id_room . "'");
while ($get_info_room_result = mysqli_fetch_assoc($get_info_room)) {
    $info_id = $get_info_room_result['id'];
    $info_id_match = $get_info_room_result['id_match'];
}

//// Get max join in room
//$get_match = mysqli_query($connect, "SELECT `max_join` FROM `type_match` WHERE `id_match`='" . $info_id_match . "'");
//while ($get_match_info_result = mysqli_fetch_assoc($get_match)) {
//    $match_max_join = $get_match_info_result['max_join'];
//}
//
//$get_joiner_round = mysqli_query($connect, "SELECT * FROM `joiner` WHERE `id_room`='" . $id_room . "'");
//$get_joiner_round_result = mysqli_num_rows($get_joiner_round);
//
//if ($match_max_join < $get_joiner_round_result) {
//    header('Location: /game/lobby.php');
//    exit;
//} else {
//    echo $match_max_join;
//    echo $get_joiner_round_result;




// If user was join the room - add data into a table `joiner`
// If user try connect to different room and at this time his
// stay at current room - it's do not permit to redirect him
$select_isset_data_query = mysqli_query($connect, "SELECT * FROM `joiner` WHERE `id`='" . $id . "' LIMIT 1");
$select_isset_data = mysqli_num_rows($select_isset_data_query);

while ($select_isset_data_query_result = mysqli_fetch_assoc($select_isset_data_query)) {
    $id_room_already_connect = $select_isset_data_query_result['id_room'];
}

if ($select_isset_data == 0) {

    $get_match = mysqli_query($connect, "SELECT `max_join` FROM `type_match` WHERE `id_match`='" . $info_id_match . "'");
    while ($get_match_info_result = mysqli_fetch_assoc($get_match)) {
        $match_max_join = $get_match_info_result['max_join'];
    }

    if ($match_max_join < 2) {
        header('Location: /game/lobby.php');
//        exit();

    } else {
        $insert_data_joiner = mysqli_query($connect, "INSERT INTO `joiner` (`id_room`, `id`) VALUES ('" . $id_room . "', '" . $id . "')");
    }

} else {
    if ($id_room != $id_room_already_connect) {
        header('Location: /game/room.php?id=' . $id_room_already_connect . '');
        exit;
    }
}



if (isset($_POST['left-room'])) {
    $get_result_search_user = mysqli_num_rows($get_info_room);
    if ($get_result_search_user == 1) {
        $delete_room = mysqli_query($connect, "DELETE FROM `rooms` WHERE `id`='" . $id . "'");
        $user_all_left_query = mysqli_query($connect, "DELETE FROM `joiner` WHERE `id_room`='" . $id_room . "'");
    } else {
        $user_left_query = mysqli_query($connect, "DELETE FROM `joiner` WHERE `id`='" . $id . "'");
    }
    header('Location: /game/lobby.php');
}

?>

<section class="lobby">
    <div class="container">
        <div class="lobby-wrapper">
            <div class="lobby-wrapper__item">
                <div class="lobby-card">
                    <div class="lobby-card__title">
                        <form method="POST">
                        <button type="submit" name="left-room">Покинуть комнату</button>
                        </form>
                    </div>
                    <div class="lobby-card__content">

                    </div>
                    <div class="lobby-card__footer">Footer<span class="card-footer__setting">dfgdf</span></div>
                </div>
            </div>

            <div class="lobby-wrapper__item">
                <div class="lobby-card">
                    <div class="lobby-card__title">Title</div>
                    <div class="lobby-card__content">Content</div>
                    <div class="lobby-card__footer">Footer <span class="card-footer__setting">dfgdf</span></div>
                </div>
            </div>

        </div>
    </div>
</section>

<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/kernel/pages/core/footer.php');
?>
<script src="<? echo $theme ?>/js/room.js"></script>
