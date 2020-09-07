$(document).ready(function () {
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