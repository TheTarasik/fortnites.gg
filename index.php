<?php
define('security', TRUE);
session_start();
function getpage() {
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
