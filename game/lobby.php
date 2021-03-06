<?php
session_name('_userSID');
session_start();
$title = 'Lobby';
require_once($_SERVER['DOCUMENT_ROOT'] . '/kernel/pages/core/header.php');

if (!(isset($_SESSION['logged']))) {
    header("Location: /?page=login");
    exit;
} else {
    $get_user = mysqli_query($connect, "SELECT * FROM `users` WHERE `login`='" . $login . "'");
    while ($get_user_result = mysqli_fetch_assoc($get_user)) {
        $id = $get_user_result['id'];
        $login = $get_user_result['login'];
        $email = $get_user_result['email'];
        $password = $get_user_result['password'];
        $user_group = $get_user_result['user_group'];
        $ip = $get_user_result['ip'];
        $token = $get_user_result['token'];
        $user_money = $get_user_result['money'];
    }
}

setcookie("token", $token, time()+60*60*24, '/');

// Redirect user to room if him exist in room
$get_isset_joiner_redirect = mysqli_query($connect, "SELECT * FROM `joiner` WHERE `id`='" . $id . "'");
while ($get_isset_joiner_redirect_result = mysqli_fetch_assoc($get_isset_joiner_redirect)) {
    $get_joiner_room = $get_isset_joiner_redirect_result['id_room'];
}
$get_isset_joiner_redirect_num_result = mysqli_num_rows($get_isset_joiner_redirect);

if ($get_isset_joiner_redirect_num_result == 1) {
    header('Location: /game/room.php?id=' . $get_joiner_room . '');
}

// Prohibit the user click button "create room" many times
$get_room_isset_query = mysqli_query($connect, "SELECT `id` FROM `rooms` WHERE `id`='" . $id . "'");
$get_room_isset = mysqli_num_rows($get_room_isset_query);
if ($get_room_isset == 1) {
    header('Location: /game/room.php?id=' . $get_joiner_room . '');
    die();
}
?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.0/jquery.min.js"></script>
<div class="message-wrapper">
    <div id="message"></div>
</div>
    <section class="lobby">
        <div class="container">
            <div class="lobby-wrapper">
                <div class="lobby-wrapper__item">
                    <div class="lobby-card">
                        <div id="lobby-loader">
                            <div class="lobby-loader__spinner">
                                <img src="<? echo $theme ?>/images/spinner.svg">
                                <span class="lobby-loader__spinner-title">Подключение...</span>
                            </div>
                        </div>
                        <div class="lobby-card__title">Доступные комнаты
                            <button class="card-title__create-lobby">Создать лобби</button>
                        </div>
                        <div class="lobby-card__content">

                            <table id="rooms-render" cellspacing="0" style="width: 100%; text-align: center;">
                                <?php
                                $room_list_result_query = mysqli_query($connect, "SELECT * FROM `rooms`");

                                while ($room_list_result = mysqli_fetch_assoc($room_list_result_query)) {
                                    $id_room_list = $room_list_result['id_room'];
                                    $id_owner = $room_list_result['id'];
                                    $bet_room_list = $room_list_result['bet'];
                                    $id_match_get = $room_list_result['id_match'];

                                    $room_list_owner = mysqli_query($connect, "SELECT `login` FROM `users` WHERE `id`='" . $id_owner . "'");
                                    while ($room_list_owner_result = mysqli_fetch_assoc($room_list_owner)) {
                                        $owner_name = $room_list_owner_result['login'];
                                    }

                                    $get_match_info = mysqli_query($connect, "SELECT * FROM `type_match` WHERE `id_match`='" . $id_match_get . "'");
                                    while ($get_match_info_result = mysqli_fetch_assoc($get_match_info)) {
                                        $match_descr = $get_match_info_result['descr'];
                                    }
                                }
                                ?>
                            </table>
                            <script>
                                function getrooms() {
                                    var user_id = '<? echo $id; ?>';
                                    $.ajax({
                                        type: 'POST',
                                        url: "/kernel/ajax/get_rooms.php",
                                        data: {get: 'get', user_id: user_id},
                                        success: function (result) {
                                            $('#rooms-render').html(result);
                                        }
                                    });
                                }

                                getrooms();
                                setInterval(function () {
                                    getrooms();
                                }, 5000);

                            </script>
                            <br><br><br>
                            <form method="POST">
                                <input type="hidden" name="xsrf_token" value="<? echo $token_generate ?>">
                                <input type="radio" class="bet" name="bet" value="0">
                                <label for="bet-0">0р.</label>

                                <input type="radio" class="bet" name="bet" value="10">
                                <label for="bet-10">10р.</label>

                                <input type="radio" class="bet" name="bet" value="50">
                                <label for="bet-50">50р.</label>

                                <label id="bet-customs" for="bet-custom">
                                    <input type="radio" id="bet-custom" name="bet-custom__radio">
                                    <input type="text" id="bet-custom__input" name="bet-custom__input">
                                </label>

                                Длительность матча
                                <select name="duration">
                                    <option value="3">До 3 побед</option>
                                    <option value="5">До 5 побед</option>
                                    <option value="7">До 7 побед</option>
                                    <option value="10">До 10 побед</option>
                                </select>

                                Тип игры
                                <select name="type_match">
                                    <option value="1">Box fight</option>
                                    <option value="2">2x2 BoxFight</option>
                                    <option value="3">3x3 BoxFight</option>
                                </select>

                                <button type="submit" name="lobby-create__submit">Создать лобби</button>
                            </form>

                            <?

                            $errors = array();

                            if (isset($_POST['lobby-create__submit'])) {
                                if ($_COOKIE['XSRF_TOKEN'] != $_POST['xsrf_token']) {
                                    $errors[] = 'Некорректный XSRF-TOKEN';
                                }

                                if (preg_match('/[^,.0-9]/', $_POST['bet'])) {
                                    $errors[] = 'Разве ставка может быть текстом?';
                                }

                                if (preg_match('/[^,.0-9]/', $_POST['bet-custom__input'])) {
                                    $errors[] = 'Поле должно содержать только цифры!';
                                }

                                if (preg_match('/[^,.0-9]/', $_POST['duration'])) {
                                    $errors[] = 'Неверный формат длительности матча!';
                                }

                                if (preg_match('/[^,.0-9]/', $_POST['type_match'])) {
                                    $errors[] = 'Неверный формат типа матча!';
                                }

                                if (!isset($_POST['bet'])) {
                                    if (!isset($_POST['bet-custom__radio'])) {
                                        $errors[] = 'Вы не указали ставку!';
                                    }
                                }

                                if (isset($_POST['bet-custom__radio'])) {
                                    if (empty($_POST['bet-custom__input'])) {
                                        $errors[] = 'Вы не указали ставку!';
                                    }
                                }

                                if (isset($_POST['duration'])) {
                                    if (empty($_POST['duration'])) {
                                        $errors[] = 'Выберите длительность матча!';
                                    }
                                }

                                if (isset($_POST['type_match'])) {
                                    if (empty($_POST['type_match'])) {
                                        $errors[] = 'Выберите тип матча!';
                                    }

                                    $get_type_match = mysqli_query($connect, "SELECT `id_match` FROM `type_match` WHERE `id_match`='" . $_POST['type_match'] . "'");
                                    while ($get_type_match_result = mysqli_fetch_assoc($get_type_match)) {
                                        $id_match = $get_type_match_result['id_match'];
                                    }

                                    if ($id_match == NULL) {
                                        $errors[] = 'Такого типа матча не существует!';
                                    }
                                }

                                if ($_POST['bet'] >= $max_bet + 1 or $_POST['bet-custom__input'] >= $max_bet + 1) {
                                    $errors[] = 'Максимальная ставка ' . $max_bet . ' рублей';
                                }

                                if ($_POST['duration'] >= $match_duration + 1) {
                                    $errors[] = 'Максимальная длительность матча ' . $match_duration . ' минут';
                                }

                                if (empty($errors)) {
                                    $hash_match = md5(rand(1000, 9999) . rand(1000, 9999) . rand(1000, 9999));
                                    $check_hash = mysqli_query($connect, "SELECT `hash` FROM `rooms` WHERE `hash`='" . $hash_match . "'");
                                    while ($check_hash_result = mysqli_fetch_assoc($check_hash)) {
                                        $checked_ray_hash = $check_hash_result['hash'];
                                    }

                                    if ($hash_match == $checked_ray_hash) {
                                        $hash_match_correct = false;
                                        echo 'Critical error! If you see this issue not first times - contact to administrator.';
                                    } else {
                                        $hash_match_correct = true;
                                    }

                                    if ($hash_match_correct == true) {
                                        setcookie("ray_id", $hash_match, time() + 60 * 60 * 24, '/');
                                        if (isset($_POST['bet-custom__radio'])) {
                                            $_POST['bet'] = $_POST['bet-custom__input'];
                                        }

                                        if ($user_money >= $_POST['bet']) {
                                        $today = date("Y-m-d H:i:s");
                                        $date_expiry = strtotime($today) + $auto_delete_room;
                                        $create_room = mysqli_query($connect, "INSERT INTO `rooms` (`id_room`, `id`, `bet`, `duration`, `id_match`, `hash`, `date`, `date_expiry`) VALUE (NULL, '" . $id . "', '" . $_POST['bet'] . "', '" . $_POST['duration'] . "', '" . $_POST['type_match'] . "', '" . $hash_match . "', '" . $today . "', '" . date("Y-m-d H:i:s", $date_expiry) . "')");
                                        $get_match_id = mysqli_query($connect, "SELECT * FROM `rooms` WHERE `hash`='" . $hash_match . "'");

                                        while ($get_match_id_results = mysqli_fetch_assoc($get_match_id)) {
                                            $id_room = $get_match_id_results['id_room'];
                                        }
                                            // Update user money get status
                                            $money_get_status_update = mysqli_query($connect, "UPDATE `users` SET `money_get_status`='0' WHERE `id`='" . $id . "'");

                                            $insert_joiner = mysqli_query($connect, "INSERT INTO `joiner` (`id_room`, `id`) VALUES ('" . $id_room . "', '" . $id . "')");
                                            header('Location: /game/room.php?id=' . $id_room . '');
                                        } // Check user money, if user don't have enough money - don't connect him to room
                                    } // HASH ISSET

                                } else {
                                    echo array_shift($errors);
                                }
                            }

                            ?>

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
<script src="<? echo $theme ?>/js/lobby.js"></script>
