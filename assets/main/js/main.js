$(document).ready(function () {
    // setTimeout(function () {
    //     var get = 1;
    //     $.ajax({
    //         type: 'POST',
    //         url: "/kernel/ajax/get_rooms.php",
    //         data: {get: get},
    //         success: function (result) {
    //             $('#dom__room--render').append(result);
    //         }
    //     });
    // }, 0);

    $('.bet').change(function(){
        $('#bet-custom').prop("checked", false);
        $('.bet').prop("checked", false);
        $(this).prop("checked", true);
    });

    $('#bet-customs, #bet-custom__input').click(function(){
        $('#bet-custom').prop("checked", true);
        $('.bet').prop("checked", false);
    });
});