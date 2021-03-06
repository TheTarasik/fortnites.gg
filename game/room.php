<?php
session_name('_userSID');
session_start();
$title = 'Room';
$id_room = $_REQUEST['id'];
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
        $token = $get_user_result['token'];
        $user_money = $get_user_result['money'];
        $money_get_status = $get_user_result['money_get_status'];
    }
}

setcookie("token", $token, time() + 60 * 60 * 24, '/');

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

if ($check_isset_room == 0) { // If room does not exist
    header('Location: /game/lobby.php');
    exit;
}

$get_info_room = mysqli_query($connect, "SELECT * FROM `rooms` WHERE `id_room`='" . $id_room . "'");
while ($get_info_room_result = mysqli_fetch_assoc($get_info_room)) {
    $info_id = $get_info_room_result['id'];
    $bet = $get_info_room_result['bet'];
    $info_id_match = $get_info_room_result['id_match'];
    $date_expiry = $get_info_room_result['date_expiry'];
}

$select_isset_data_query = mysqli_query($connect, "SELECT * FROM `joiner` WHERE `id`='" . $id . "' LIMIT 1");
$select_isset_data = mysqli_num_rows($select_isset_data_query);

$get_match = mysqli_query($connect, "SELECT `max_join` FROM `type_match` WHERE `id_match`='" . $info_id_match . "'");
while ($get_match_info_result = mysqli_fetch_assoc($get_match)) {
    $match_max_join = $get_match_info_result['max_join'];
}

$get_joiner_round = mysqli_query($connect, "SELECT * FROM `joiner` WHERE `id_room`='" . $id_room . "'");
$get_joiner_round_result = mysqli_num_rows($get_joiner_round);

while ($select_isset_joiner = mysqli_fetch_assoc($select_isset_data_query)) {
    $true_room_id = $select_isset_joiner['id_room'];
}

if ($match_max_join < $get_joiner_round_result) { // If count of user in single room more then in indicated type of match - redirect him to lobby.
    $delete_if_isset = mysqli_query($connect, "DELETE FROM `joiner` WHERE `id`='" . $id . "'");
    header('Location: /game/lobby.php');
    exit();
}

if ($select_isset_data == 1) { // If user try connect to different room.
    if ($id_room != $true_room_id) {
        header('Location: /game/room.php?id=' . $true_room_id . '');
        exit();
    }
}

//echo $match_max_join;
//echo $get_joiner_round_result;

if (isset($_POST['left-room'])) {


    // Get creator of room
    $get_creator_of_room_query = mysqli_query($connect, "SELECT `id` FROM `rooms` WHERE `id_room`='" . $id_room . "'");
    while ($get_creator_of_room_result = mysqli_fetch_assoc($get_creator_of_room_query)) {
        $get_creator_of_room = $get_creator_of_room_result['id'];
    }

    // Get creator ready status
    $get_creator_ready_status_query = mysqli_query($connect, "SELECT `ready` FROM `joiner` WHERE `id`='" . $get_creator_of_room . "'");
    while ($get_creator_ready_status_result = mysqli_fetch_assoc($get_creator_ready_status_query)) {
        $get_creator_ready_status = $get_creator_ready_status_result['ready'];
    }

    // Get user who ready
    $get_user_who_ready_query = mysqli_query($connect, "SELECT `id` FROM `joiner` WHERE `ready`='1' AND `id_room`='" . $id_room . "'");
    while ($get_user_who_ready_result = mysqli_fetch_assoc($get_user_who_ready_query)) {
        $get_user_who_ready = $get_user_who_ready_result['id'];
    }


    // Get enemy from joiner
    $get_enemy_query = mysqli_query($connect, "SELECT * FROM `joiner` WHERE `id` NOT LIKE '" . $get_creator_of_room . "' AND `id_room`='" . $id_room ."'");
    while ($get_enemy_result = mysqli_fetch_assoc($get_enemy_query)) {
        $get_enemy = $get_enemy_result['id'];
        $get_enemy_ready_status = $get_enemy_result['ready'];
    }

    // Get enemy from users
    $get_enemy_from_users_query = mysqli_query($connect, "SELECT * FROM `users` WHERE `id`='" . $get_enemy . "'");
    while ($get_enemy_from_users_result = mysqli_fetch_assoc($get_enemy_from_users_query)) {
        $get_enemy_money = $get_enemy_from_users_result['money'];
    }

    // Money get status enemy
    $money_get_status_enemy_query = mysqli_query($connect, "SELECT `money_get_status` FROM `users` WHERE `id`='" . $get_enemy . "'");
    while ($money_get_status_enemy_result = mysqli_fetch_assoc($money_get_status_enemy_query)) {
        $money_get_status_enemy = $money_get_status_enemy_result['money_get_status'];
    }

    // Get all ready status
    $get_all_ready_status_query = mysqli_query($connect, "SELECT `ready` FROM `joiner` WHERE `ready` NOT LIKE '0' AND `id_room`='" . $id_room . "'");
    $get_all_ready_status = mysqli_num_rows($get_all_ready_status_query);

    if ($get_all_ready_status != 2) {

        if ($id == $get_creator_of_room) {
            // Creator
            if ($get_creator_ready_status == 1) {
                $wait_balance = $user_money + $bet; // Generate new balance
                if ($money_get_status == 0) {
                    $return_balance = mysqli_query($connect, "UPDATE `users` SET `money`='" . $wait_balance . "' WHERE `id`='" . $get_creator_of_room . "'");
                    $money_get_status_update = mysqli_query($connect, "UPDATE `users` SET `money_get_status`='1' WHERE `id`='" . $get_creator_of_room . "'");
                }
            }

            if ($get_enemy_ready_status == 1) {
                $enemy_wait_balance = $get_enemy_money + $bet; // Generate new balance
                if ($money_get_status_enemy == 0) {
                    $money_get_status_update = mysqli_query($connect, "UPDATE `users` SET `money_get_status`='1' WHERE `id`='" . $get_enemy . "'");
                    $return_balance = mysqli_query($connect, "UPDATE `users` SET `money`='" . $enemy_wait_balance . "' WHERE `id`='" . $get_enemy . "'");
                }
            }

            $delete_room = mysqli_query($connect, "DELETE FROM `rooms` WHERE `id`='" . $get_creator_of_room . "'");
            $user_left_query = mysqli_query($connect, "DELETE FROM `joiner` WHERE `id_room`='" . $id_room . "'");

        }


        if ($id == $get_enemy) {
            // Not creator
            if ($get_enemy_ready_status == 1) {
                $enemy_wait_balance = $get_enemy_money + $bet; // Generate new balance
                if ($money_get_status_enemy == 0) {
                    $money_get_status_update = mysqli_query($connect, "UPDATE `users` SET `money_get_status`='1' WHERE `id`='" . $get_enemy . "'");
                    $return_balance = mysqli_query($connect, "UPDATE `users` SET `money`='" . $enemy_wait_balance . "' WHERE `id`='" . $get_enemy . "'");
                }
            }

            $user_left_query = mysqli_query($connect, "DELETE FROM `joiner` WHERE `id`='" . $get_enemy . "'");

        }

    } else {

        if ($get_all_ready_status == 2) {
            if ($id == $get_creator_of_room) {
                // Creator

                if ($get_enemy_ready_status == 1) {
                    $enemy_wait_balance = $get_enemy_money + ($bet * 2); // Generate new balance
                    if ($money_get_status_enemy == 0) {
                        $money_get_status_update = mysqli_query($connect, "UPDATE `users` SET `money_get_status`='1' WHERE `id`='" . $get_enemy . "'");
                        $return_balance = mysqli_query($connect, "UPDATE `users` SET `money`='" . $enemy_wait_balance . "' WHERE `id`='" . $get_enemy . "'");
                    }
                }

                $delete_room = mysqli_query($connect, "DELETE FROM `rooms` WHERE `id`='" . $get_creator_of_room . "'");
                $user_left_query = mysqli_query($connect, "DELETE FROM `joiner` WHERE `id_room`='" . $id_room . "'");

            }

            if ($id == $get_enemy) {
                // Not creator
                $get_user_money_again_query = mysqli_query($connect, "SELECT * FROM `users` WHERE `id`='" . $get_creator_of_room . "'");
                while ($get_user_money_again_result = mysqli_fetch_assoc($get_user_money_again_query)) {
                    $user_money_again = $get_user_money_again_result['money'];
                }

                if ($get_creator_ready_status == 1) {
                    $wait_balance = $user_money_again + ($bet * 2); // Generate new balance
                    if ($money_get_status == 0) {
                        $return_balance = mysqli_query($connect, "UPDATE `users` SET `money`='" . $wait_balance . "' WHERE `id`='" . $get_creator_of_room . "'");
                        $money_get_status_update = mysqli_query($connect, "UPDATE `users` SET `money_get_status`='1' WHERE `id`='" . $get_creator_of_room . "'");
                    }
                }

                $delete_room = mysqli_query($connect, "DELETE FROM `rooms` WHERE `id`='" . $get_creator_of_room . "'");
                $user_left_query = mysqli_query($connect, "DELETE FROM `joiner` WHERE `id`='" . $get_enemy . "'");
            }
        }
    } // get all ready status == 2

}

?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.0/jquery.min.js"></script>
<div class="message-wrapper">
    <div id="message"></div>
</div>
<section class="room">
    <div id="wait-spinner__wrapper">
        <div class="wait-spinner__item">
            <img src="<? echo $theme ?>/images/spinner-room.svg">
        </div>
    </div>
    <div class="container">
        <div class="lobby-wrapper">
            <div class="lobby-wrapper__item item-content">
                <div class="lobby-card">
                    <div class="lobby-card__title">
                        <button id="leave" class="modal-show">Покинуть комнату</button>
                    </div>
                    <div class="lobby-card__content">
                        <button id="ready" class="modal-show">Готов</button>
                    </div>
                    <div class="lobby-card__footer">Footer<span class="card-footer__setting"><div id="timer"></div></span></div>
                </div>

                <div class="lobby-card">
                    <div class="lobby-card__title">
                        Сейчас в лобби:
                    </div>
                    <div class="lobby-card__content">
                      <div id="users-render"></div>
                    </div>
                    <div class="lobby-card__footer"><span class="card-footer__date-expiry">Комната будет удалена <? echo $date_expiry ?></span><span class="card-footer__setting">dfgdf</span></div>
                </div>
            </div>

            <div class="lobby-wrapper__item item-chat">
                <div class="lobby-card">
                    <div class="lobby-card__title">Chat<span id="message-chat"> оповещение</span></div>
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

<div class="modal-wrapper ready">
    <div class="modal modal-mini">
        <div class="modal-navbar">
            <h3>С вашего баланса будут списаны средства</h3>
        </div>

        <div class="modal-content">
            <button id="accept-ready" class="button button-accept button-fullwidth button-m__10">Подтвердить</button>
            <button id="ready" class="button button-decline button-fullwidth modal-close button-m__10">Отменить</button>
        </div>

        <div class="modal-footer">

        </div>
    </div>
    <div id="ready" class="modal-shadow"></div>
</div>

<div class="modal-wrapper leave">
    <div class="modal modal-mini">
        <div class="modal-navbar">
            <h3>Вы уверены что хотите выйти из комнаты?</h3>
            <span id="exit-warning"></span>
        </div>

        <div class="modal-content">
            <form method="POST">
                <button type="submit" class="button button-accept button-fullwidth button-m__10" name="left-room">Покинуть комнату</button>
            </form>
            <button id="leave" class="button button-decline button-fullwidth modal-close button-m__10">Отменить</button>
        </div>

        <div class="modal-footer">

        </div>
    </div>
    <div id="ready" class="modal-shadow"></div>
</div>

<div class="info-room">
    <span class="info-room__item" title="ray_id"><? echo $_COOKIE['ray_id'] ?></span>
</div>

<script>

    $(document).ready(function () {
        var room_id = '<? echo $id_room ?>';
        var user_id = '<? echo $id ?>';
        var connect = new WebSocket('ws://127.0.0.1:4433');

        // window.onbeforeunload = function() {
        //     return true;
        // };

        function stats() {
            $.ajax({
                type: 'POST',
                url: "/kernel/ajax/stats.php",
                data: {stats: 'stats', id: user_id, room_id: room_id, token: $.cookie('token')},
                success: function (result) {
                    if (result !== '') {
                        var data = JSON.parse(result);

                        if (data.active === 0) {
                            document.location.href = "/game/lobby.php";
                        }

                        if (data.ready === 1) {
                            $('button#ready').prop('disabled', false);
                            $('.item-chat').addClass('active');
                            $('#wait-spinner__wrapper').css('display', 'none');

                            //console.log('all ready');

                            if ($('.item-chat').hasClass('active')) {
                                $('.item-content').addClass('active');
                            }
                        } else {
                            $('button#ready').prop('disabled', true);
                            $('.item-chat').removeClass('active');
                            $('.item-content').removeClass('active');
                            $('#wait-spinner__wrapper').css('display', 'block');
                        }

                        if (data.status === 'run') {
                            $('#timer').text(data.mini_countdown);
                        } else {
                            $('#timer').text(data.status);
                        }

                        if (data.ready_status === 'Ok') {
                            if (data.bet !== '0') {
                                $('#exit-warning').text('Внимание! Если вы выйдете из комнаты игра будет автоматически завершена не в вашу пользу, и все деньги перейдут другому игроку!');
                            }
                        }

                        $('#users-render').html(JSON.parse(data.joiner_render));
                    }
                },
                error: function(){
                    var xhr_content_blocked = {
                        room_id: room_id,
                        user_id: user_id,
                        action: 'xhr_content_blocked',
                        token: $.cookie('token')
                    };

                    setTimeout(function () {
                        connect.send(JSON.stringify(xhr_content_blocked));
                    }, 500);
                }
            });
        }

        stats();
        setInterval(stats, 1000);

        connect.onopen = function (e) {
            console.log("Connection established!");
        };

        connect.onmessage = function (e) {
            console.log(e.data);
            var data = JSON.parse(e.data);

            if (data.room_id === room_id) {

                var chat_render = '<tr> <td><div class="chat-message__wrapper ' + data.user_id + '"> <div class="chat-message__wrapper-item chat-message__userpic"> <div class="userpic-wrapper"> <img src="/uploads/' + data.login + '.jpg"></div> </div>  <div class="chat-message__wrapper-item"> <div class="chat-message__login">' + data.login + '</div> <div class="chat-message__message">' + data.msg + '<span class="chat-message__date">' + data.date + '</span></div> </div>  </div> </td></tr>';

                if (data.action === 'chat_sender') {
                    if (typeof (data.msg) != "undefined" && data.msg !== null) {
                        // $.cookie('token', data.token, { expires: 1, path: '/' });
                        $('#chat-render').append(chat_render);
                        scrollchat();
                    }
                    if (user_id === data.user_id) {
                        $('.chat-message__wrapper.' + data.user_id).addClass('incoming');
                    } else {
                        if (localStorage.getItem('message_chat') === '1') {
                                message();
                        }
                    }
                }

                if (data.action == 'leave' && data.token == $.cookie('token')) {
                    if (data.user_id == user_id) {
                        document.location.href = "/game/lobby.php";
                    }
                }

                if (data.action == 'ready_status' && data.token == $.cookie('token')) {
                    if (data.user_id == user_id) {
                        if (data.status === 0) {
                            $('#message').append(`<div class='message-timeout'>Не достаточно средств!</div>`);
                            $('.message-timeout').addClass('error');
                        }

                        if (data.status === 1) {
                            $('#message').append(`<div class='message-timeout' style=''>Вы приняли участие в игре!</div>`);
                            $('.message-timeout').addClass('success');
                            $('#balance').text(data.current_balance + ' ');
                        }

                        if (data.status === 2) {
                            $('#message').append(`<div class='message-timeout'>Вы уже подтвердили игру!</div>`);
                            $('.message-timeout').addClass('warning');
                        }
                    }
                }

                if (data.action == 'xhr_content_blocked' && data.token == $.cookie('token')) {
                    if (data.user_id == user_id) {
                        document.location.href = "/game/lobby.php";
                    }
                }
            }
        };


        // window.addEventListener('unload', function () {
        //     navigator.sendBeacon('/kernel/ajax/disconnect.php?=' + $.cookie('token'), 'entry');
        // }, false);


            connect.onclose = function () {
                $('#message').append("<div class='message-timeout'>Что-то не так! Проверьте соединение с интернетом...</div>");
                $('.message-timeout').addClass('error');
            };

            function scrollchat() {
                var chat_height = $('#chat-render').height();
                setTimeout(function () {
                    $('#chat-render__text').val('');
                }, 1);
                $(".chat-wrapper").animate({scrollTop: chat_height}, 50);
            }

            function blocksendmessage() {
                $('#chat-render__text').prop('disabled', true);
                $('#char_render__submit').prop('disabled', true);
                $('#chat-render__text').attr('placeholder', 'Подождите 3 секунды...');
                setTimeout(function () {
                    $('#chat-render__text').prop('disabled', false);
                    $('#char_render__submit').prop('disabled', false);
                    $('#chat-render__text').attr('placeholder', 'Напишите сообщение...');
                }, 3000);
            }

        var chatrender__text = document.getElementById('chat-render__text');

        chatrender__text.oninput = () => {
            if(chatrender__text.value.charAt(0) === ' ') {
                chatrender__text.value = '';
                $('#char_render__submit').prop('disabled', true);
            } else {
                $('#char_render__submit').prop('disabled', false);
            }
        };

            $(document).keypress(function(e) {
                var data = {
                    room_id: room_id,
                    user_id: user_id,
                    action: 'chat_sender',
                    msg: $('#chat-render__text').val(),
                    token: $.cookie('token')
                };
                if (e.which == 13) {
                    if ($('#chat-render__text').val() == '') {
                        $('#char_render__submit').prop('disabled', true);
                    } else {
                        $('#char_render__submit').prop('disabled', false);
                        connect.send(JSON.stringify(data));
                        scrollchat();
                        blocksendmessage();
                    }
                }

            });

            $('#char_render__submit').on('click', function () {
                var data = {
                    room_id: room_id,
                    user_id: user_id,
                    action: 'chat_sender',
                    msg: $('#chat-render__text').val(),
                    token: $.cookie('token')
                };
                connect.send(JSON.stringify(data));
                scrollchat();
                blocksendmessage();
            });

            $('.left-room').on('click', function () {
                var data = {
                    room_id: room_id,
                    user_id: user_id,
                    action: 'leave',
                    token: $.cookie('token')
                };
                connect.send(JSON.stringify(data));
            });

            $('#accept-ready').on('click', function () {
                var data = {
                    room_id: room_id,
                    user_id: user_id,
                    action: 'ready_status',
                    token: $.cookie('token')
                };
                connect.send(JSON.stringify(data));
            });


        // function countdown() {
        //     $.ajax({
        //         type: 'POST',
        //         url: "/kernel/ajax/countdown.php",
        //         data: {countdown: 'countdown', id: user_id, room_id: room_id, token: $.cookie('token')},
        //         success: function (result) {
        //             let data = JSON.parse(result);
        //             if (data.status === 'run') {
        //                 $('#timer').text(data.mini_countdown);
        //             } else {
        //                 $('#timer').text(data.status);
        //             }
        //         },
        //         error: function(){
        //             var xhr_content_blocked = {
        //                 room_id: room_id,
        //                 user_id: user_id,
        //                 action: 'xhr_content_blocked',
        //                 token: $.cookie('token')
        //             };
        //
        //             setTimeout(function () {
        //                 connect.send(JSON.stringify(xhr_content_blocked));
        //             }, 500);
        //         }
        //     });
        // }
        //
        // countdown();
        // setInterval(countdown, 1000);







        //     function leavecheck() {
        //             $.ajax({
        //                 type: 'POST',
        //                 url: "/kernel/ajax/timer.php",
        //                 data: {timer: 'timer', id: user_id, room_id: room_id},
        //                 success: function (result) {
        //                     console.log(result);
        //                 },
        //                 error: function(){
        //                     var xhr_content_blocked = {
        //                         room_id: room_id,
        //                         user_id: user_id,
        //                         action: 'xhr_content_blocked',
        //                         token: $.cookie('token')
        //                     };
        //
        //                     setTimeout(function () {
        //                         connect.send(JSON.stringify(xhr_content_blocked));
        //                     }, 500);
        //                 }
        //             });
        //     }
        //
        // setInterval(leavecheck, 1000);








        // setInterval(function() {
        // var h,m,s,t;
        // var seconds = $.cookie('timer');
        //
        //             $.ajax({
        //                 type: 'POST',
        //                 url: "/kernel/ajax/timer.php",
        //                 data: {timer: 'timer', room_id: room_id, seconds: seconds},
        //                 success: function (result) {
        //                     var data = JSON.parse(result);
        //                     var seconds = data.timer_current;
        //
        //                     seconds--;
        //                     $.cookie('timer', seconds, { expires: 1, path: '/' });
        //
        //
        //                     h = seconds / 3600 ^ 0,
        //                         m = (seconds - h * 3600) / 60 ^ 0,
        //                         s = seconds - h * 3600 - m * 60,
        //                         time = (m < 10 ? "0" + m : m) + ":" + (s < 10 ? "0" + s : s);
        //
        //                     $("#timer").text(time);
        //                 }
        //             });
        //
        //         // if ($('#timer').text() == '00:00') {
        //         //     alert('opa');
        //         //     seconds ;
        //         // }
        //
        // }, 1000);



        });
</script>

<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/kernel/pages/core/footer.php');
?>
<script src="<? echo $theme ?>/js/room.js"></script>