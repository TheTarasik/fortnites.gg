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

$select_isset_data_query = mysqli_query($connect, "SELECT * FROM `joiner` WHERE `id`='" . $id . "' LIMIT 1");
$select_isset_data = mysqli_num_rows($select_isset_data_query);

$get_match = mysqli_query($connect, "SELECT `max_join` FROM `type_match` WHERE `id_match`='" . $info_id_match . "'");
while ($get_match_info_result = mysqli_fetch_assoc($get_match)) {
    $match_max_join = $get_match_info_result['max_join'];
}

$get_joiner_round = mysqli_query($connect, "SELECT * FROM `joiner` WHERE `id_room`='" . $id_room . "'");
$get_joiner_round_result = mysqli_num_rows($get_joiner_round);



if ($select_isset_data == 0) {
    $insert_joiner = mysqli_query($connect, "INSERT INTO `joiner` (`id_room`, `id`) VALUES ('" . $id_room . "', '" . $id . "')");
}

if ($match_max_join < $get_joiner_round_result) {
    $delete_if_isset = mysqli_query($connect, "DELETE FROM `joiner` WHERE `id`='" . $id . "'");
    header('Location: /game/lobby.php');
    exit();
}

echo $match_max_join;
echo $get_joiner_round_result;

if (isset($_POST['left-room'])) {
    $user_left_query = mysqli_query($connect, "DELETE FROM `joiner` WHERE `id`='" . $id . "'");
    if ($id == $info_id) {
        $delete_room = mysqli_query($connect, "DELETE FROM `rooms` WHERE `id`='" . $id . "'");
        $user_left_query = mysqli_query($connect, "DELETE FROM `joiner` WHERE `id_room`='" . $id_room . "'");
    }
    header('Location: /game/lobby.php');
}

?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.0/jquery.min.js"></script>
<section class="lobby">
    <div class="container">
        <div class="lobby-wrapper">
            <div class="lobby-wrapper__item">
                <div class="lobby-card">
                    <div class="lobby-card__title">
                        <form method="POST">
                            <button type="submit" class="left-room" name="left-room">Покинуть комнату</button>
                        </form>
                    </div>
                    <div class="lobby-card__content">

                    </div>
                    <div class="lobby-card__footer">Footer<span class="card-footer__setting">dfgdf</span></div>
                </div>
            </div>

            <div class="lobby-wrapper__item">
                <div class="lobby-card">
                    <div class="lobby-card__title">Chat</div>
                    <div class="lobby-card__content chat-height">
                        <div class="chat-wrapper">
                            <table id="chat-render" style="width: 100%;">
                            </table>
                        </div>
                    </div>
                    <div class="room-chat__footer"><input id="chat-render__text" placeholder="Напишите сообщение..."><span class="room-chat__message-send"><button id="char_render__submit" disabled>Отправить</button></span></div>
                </div>
                <br>
                <div class="lobby-card">
                    <div class="lobby-card__title">Title</div>
                    <div class="lobby-card__content">Content</div>
                    <div class="lobby-card__footer">Footer <span class="card-footer__setting">dfgdf</span></div>
                </div>
            </div>

        </div>
    </div>
</section>

<script>
    $(document).ready(function () {
        var room_id = '<? echo $id_room ?>';
        var user_id = '<? echo $id ?>';
        var connect = new WebSocket('ws://127.0.0.1:4433');

        connect.onopen = function (e) {
            console.log("Connection established!");
        };

        connect.onmessage = function (e) {
            console.log(e.data);
            var data = JSON.parse(e.data);

            if (data.room_id == room_id) {
                var chat_render = '<tr> <td><div class="chat-message__wrapper"> <div class="chat-message__wrapper-item chat-message__userpic"> <img src="/uploads/' + data.login + '.jpg"> </div>  <div class="chat-message__wrapper-item "> ' + data.login + ' <br> ' + data.msg + ' </div>  </div> </td></tr>';

                if (data.action == 'chat_sender') {
                    if (typeof (data.msg) != "undefined" && data.msg !== null) {
                        $('#chat-render').append(chat_render);
                        scrollchat();
                    }
                }

                if (data.action == 'leave') {
                    if (data.user_id == user_id) {
                        document.location.href = "/game/lobby.php";
                    }
                }
            }
        };

            connect.onclose = function (e) {
                connect.send("Disconnect!");
            };

            function scrollchat() {
                var chat_height = $('#chat-render').height();
                setTimeout(function () {
                    $('#chat-render__text').val('');
                }, 1);
                $(".chat-wrapper").animate({scrollTop: chat_height}, 50);
            }

            $('#chat-render__text').keyup(function () {
                if ($('#chat-render__text').val() == '') {
                    $('#char_render__submit').prop('disabled', true);
                } else {
                    $('#char_render__submit').prop('disabled', false);
                }
            });

            $(document).keypress(function(e) {
                var data = {
                    room_id: room_id,
                    user_id: <? echo $id ?>,
                    action: 'chat_sender',
                    msg: $('#chat-render__text').val()
                };
                if (e.which == 13) {
                    if ($('#chat-render__text').val() == '') {
                        $('#char_render__submit').prop('disabled', true);
                    } else {
                        $('#char_render__submit').prop('disabled', false);
                        connect.send(JSON.stringify(data));
                        scrollchat();
                    }
                }

            });

            $('#char_render__submit').on('click', function () {
                var data = {
                    room_id: room_id,
                    user_id: <? echo $id ?>,
                    action: 'chat_sender',
                    msg: $('#chat-render__text').val()
                };
                scrollchat();
                connect.send(JSON.stringify(data));
            });

            $('.left-room').on('click', function () {
                var data = {
                    room_id: room_id,
                    user_id: <? echo $id ?>,
                    action: 'leave'
                };
                connect.send(JSON.stringify(data));
            });

        });
</script>

<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/kernel/pages/core/footer.php');
?>
<script src="<? echo $theme ?>/js/room.js"></script>