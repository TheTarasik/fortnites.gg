let host = document.location.origin;
let audio = new Audio();
audio.canPlayType('audio/ogg; codecs="vorbis"');

function message() {
    audio.src = host + '/kernel/sounds/message.ogg';
    audio.autoplay = true;
}