$(document).ready(function () {
    // Message
    //$.cookie('message', '1', { expires: 365 * 10, path: '/' });
    localStorage.setItem('message_chat', 1);

    $('#message-chat').on('click', function () {
        //$.cookie('message', '0', { expires: 365 * 10, path: '/' });
        localStorage.setItem('message_chat', 0);
    });

    // Modal
    $('.modal-show').on('click', function () {
        var modal_get = $(this).attr('id');
        $('.modal-wrapper' + '.' + modal_get).addClass('active');
    });

    $('.modal-close').on('click', function () {
        var modal_get = $(this).attr('id');
        $('.modal-wrapper' + '.' + modal_get).removeClass('active');
    });

    $('.modal-shadow').on('click', function () {
        var modal_get = $(this).attr('id');
        $('.modal-wrapper' + '.' + modal_get).removeClass('active');
    });

    // For accept-ready
    $('#accept-ready').on('click', function () {
        $('.modal-wrapper.ready').removeClass('active');
    });

});