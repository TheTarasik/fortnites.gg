let host = document.location.origin;

function message() {
    var audio = new Audio();
    audio.src = host + '/kernel/sounds/message.ogg';
    audio.autoplay = true;
}