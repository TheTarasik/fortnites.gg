

// function refreshPage(){
//     var login = $('#login').val();
//     $.post( "",
//         {
//             user_id:login
//         }, setTimeout(refreshPage, 10000)
//         );
// }
// refreshPage();

// function Unloader(){
//     var o = this;
//     this.unload = function(evt){
//         var message = "Вы действительно хотите покинуть страницу?";
//         if (typeof evt == "undefined") evt = window.event;
//         if (evt) evt.returnValue = message;
//         return message;
//         if(confirm(message)) {
//             var login = $('#login').val();
//             $.post( "http://fortnite.gg/kernel/ajax/disconnect.php", {user_id:login});
//         }
//     };
//
//     this.resetUnload = function()
//     {
//         $(window).off('beforeunload', o.unload);
//         setTimeout(function(){
//             $(window).on('beforeunload', o.unload);
//         }, 1000);
//     };
//
//     this.init = function()
//     {
//         $(window).on('beforeunload', o.unload);
//         $('a').on('click', o.resetUnload);
//         $(document).on('submit', 'form', o.resetUnload);
//
//         $(document).on('keydown', function(event){
//             if((event.ctrlKey && event.keyCode == 116) || event.keyCode == 116 || event.keyCode == 13){
//                 if(confirm('Вы уверены, что хотите обновить страницу')){
//                     o.resetUnload();
//                     var login = $('#login').val();
//                     $.post( "http://fortnite.gg/kernel/ajax/disconnect.php", {user_id:login});
//                 } else {
//                     return false;
//                 }
//             }
//         });
//     };
//     this.init();
// }
//
// $(function(){
//     if(typeof window.obUnloader != 'object')  window.obUnloader = new Unloader();
// });

