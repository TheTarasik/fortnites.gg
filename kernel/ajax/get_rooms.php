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
    @header ( 'Location: /');
    die();
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/kernel/data/dbconfig.php');

function protectsqlinj($connect, $value) {
    return mysqli_real_escape_string($connect, $value);
}

$_POST['user_id'] = protectsqlinj($connect, $_POST['user_id']);

if(isset($_POST['get']) && !empty($_POST['get'])) {

    $get_rooms = mysqli_query($connect, "SELECT * FROM `rooms` LIMIT 5");

    echo '<tr>
                                    <th>Ставка</th>
                                    <th>Создатель</th>
                                    <th>Тип игры</th>
                                    <th></th>
                                </tr>';
    while ($get_rooms_info = mysqli_fetch_assoc($get_rooms)) {
        $id_room = $get_rooms_info['id_room'];
        $id = $get_rooms_info['id'];
        $bet = $get_rooms_info['bet'];
        $duration = $get_rooms_info['duration'];
        $id_match = $get_rooms_info['id_match'];
        $hash = $get_rooms_info['hash'];
        $get_login_by_id = mysqli_query($connect, "SELECT * FROM `users` WHERE `id`='" . $id . "'");
        while ($get_login_by_id_result = mysqli_fetch_assoc($get_login_by_id)) {
            $login = $get_login_by_id_result['login'];
        }

        $get_match = mysqli_query($connect, "SELECT * FROM `type_match` WHERE `id_match`='" . $id_match . "'");
        while ($get_match_info_result = mysqli_fetch_assoc($get_match)) {
            $match_descr = $get_match_info_result['descr'];
        }
        echo '<tr>
           <td>' . $bet . '</td>
            <td>' . $login . '</td>
            <th>' . $match_descr . '</th>
            <td><a class="button button-default button-bordered button-fullwidth room-join" id="' . $id_room . '">Войти</a></td>
         </tr>';

    }

    echo "
<script>
                                 $('.room-join').on('click', function () {
                                    var id_room = $(this).attr('id');
                                    var id = '" . $_POST['user_id'] . "';
                                        console.log('Connect handler start!', 'id_room -', id_room, 'id -', id);
                                        $('#lobby-loader').css('display', 'block');
                                        $(this).css('cursor', 'not-allowed');
                                        setTimeout(function () {
                                            $.ajax({
                                                type: 'POST',
                                                url: '/kernel/ajax/room_join.php',
                                                data: {id_room: id_room, id: id, token: $.cookie('token')},
                                                success: function (result) {
                                                    if (result == 3) {
                                                        $('#message').append(`<div class='message-timeout'>Не достаточно средств!</div>`);
                                                        $('.message-timeout').addClass('error');
                                                        $('#lobby-loader').css('display', 'none');
                                                        $('.room-join').css('cursor', 'pointer');
                                                    }
                                                    if (result == 2) {
                                                        document.location.href = '/game/lobby.php';
                                                    }
                                                    if (result == 1) {
                                                            document.location.href = '/game/lobby.php';
                                                    }
                                                    if (result == 0) {
                                                       document.location.href = '/game/room.php?id=' + id_room;
                                                    }
                                                    
                                                    setTimeout(function() {
                                                      console.log(result);
                                                    }, 2000);
                                                    
                                                }
                                            });
                                        }, 3000); // Connect time
                                });
                            </script>
    ";
}
?>