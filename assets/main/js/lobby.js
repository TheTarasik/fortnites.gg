$(document).ready(function () {

    var connect = new WebSocket('ws://127.0.0.1:4433');

    connect.onopen = function (e) {
        console.log("Connection established!");
    };

    connect.onmessage = function (e) {
        console.log(e.data);
    };

    connect.onclose = function (e) {
        connect.send("Disconnect!");
    };

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