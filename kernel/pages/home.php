<?php
$title = 'Home';
require_once($_SERVER['DOCUMENT_ROOT'] . '/kernel/pages/core/header.php');

if (isset($_SESSION['logged'])) {
    header("Location: /game/lobby.php");
    exit;
}
?>

HOME

<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/kernel/pages/core/footer.php');
?>
