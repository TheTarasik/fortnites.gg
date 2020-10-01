<?php
$title = 'Profile';
require_once($_SERVER['DOCUMENT_ROOT'] . '/kernel/pages/core/header.php');

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

// If user was join the room - add data into a table `joiner`
// If user try connect to different room and at this time his
// stay at current room - it's do not permit to redirect him
$select_isset_data_query = mysqli_query($connect,"SELECT * FROM `joiner` WHERE `id`='" . $id . "' LIMIT 1");
$select_isset_data = mysqli_num_rows($select_isset_data_query);

while($select_isset_data_query_result = mysqli_fetch_assoc($select_isset_data_query)) {
    $id_room_already_connect = $select_isset_data_query_result['id_room'];
}

if ($select_isset_data == 0) {
    $insert_data_joiner = mysqli_query($connect, "INSERT INTO `joiner` (`id_room`, `id`) VALUES ('" . $id_room . "', '" . $id . "')");
} else {
    if ($id_room != $id_room_already_connect) {
        header('Location: /game/room.php?id=' . $id_room_already_connect . '');
    }
}

?>



<? echo $login; ?>

<a href="/?page=logout">Выход</a>

<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/kernel/pages/core/footer.php');
?>