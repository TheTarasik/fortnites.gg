<?php
$title = 'Exit';
require_once($_SERVER['DOCUMENT_ROOT'] . '/kernel/pages/core/header.php');

unset($_SESSION['logged']);
session_destroy();
header('Location: /?page=home')
?>

<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/kernel/pages/core/footer.php');
?>
