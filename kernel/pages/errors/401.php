<?php
session_start();
$title = 'Ошибка 401! Ошибка авторизации.';
require_once($_SERVER['DOCUMENT_ROOT'] . '/kernel/pages/core/header.php');
?>

401 палучаеться

<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/kernel/pages/core/footer.php');
?>