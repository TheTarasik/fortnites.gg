<?php
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest' || strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) < 1) die ();
require_once($_SERVER['DOCUMENT_ROOT'] . '/kernel/data/dbconfig.php');

if(isset($_POST['get']) && !empty($_POST['get'])) {

$get_rooms = mysqli_query($connect, "SELECT * FROM `rooms` LIMIT 5");
while ($get_rooms_info = mysqli_fetch_assoc($get_rooms)) {
$id_room = $get_rooms_info['id_room'];
$id = $get_rooms_info['id'];
$bet = $get_rooms_info['bet'];
$duration = $get_rooms_info['duration'];
$id_match = $get_rooms_info['id_match'];
$hash = $get_rooms_info['hash'];


echo '
                            <td>' . $bet . '</td>      
                            <td>' . $id . '</td>
                            <td>' . $bet . '</td>     
                            <td><a id=' . $id_room . '>Войти</a></td> 
                      
<!--<script>
    $("#" +  $id_room ).on("click", function () {
        $(this).css("cursor", "not-allowed");
            setTimeout(function () {
                $.ajax({
                    type: "POST",
                       url: "/kernel/ajax/room_join.php",
                           data: {id_room:  $id_room , id:  $id},
                            success: function (result) {
                                if (result == 1) {
                                       document.location.href = "/game/lobby.php";
                                }
if (result == 0) {
document.location.href = "/game/room.php?id=" +  $id_room ;
}
}
});
}, 2000);
});

</script>
-->
';
}
//    echo '
//    <script>
//    var id_room = ' . $id_room . ',
//        id = ' . $id .';
//    $("#" + id_room).on("click", function () {
//
//        $(this).css("cursor", "not-allowed");
//        setTimeout(function () {
//            $.ajax({
//                type: "POST",
//                url: "/kernel/ajax/room_join.php",
//                data: {id_room: id_room, id: id},
//                success: function (result) {
//                    if (result == 1) {
//                        document.location.href = "/game/lobby.php";
//                    }
//                    if (result == 0) {
//                        document.location.href = "/game/room.php?id=" + id_room;
//                    }
//                }
//            });
//        }, 2000);
//    });
//
//</script>';
}
?>



