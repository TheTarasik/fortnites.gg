<?php
define('security', TRUE);
session_name('_userSID');
session_start();
function getpage() {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/kernel/data/config.php');
    $request = $_REQUEST['page'];
    $url = 'kernel/pages/';
    if (isset($request) && !empty($request)) {
        if (file_exists($url . $request . ".php")) {
            require_once($url . $request . ".php");
        } else {
            require_once($url . 'errors/404.php');
        }
    } else {
        header("Location: /?page=home");
    }
}

getpage();
?>
