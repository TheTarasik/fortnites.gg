<?php
$title = 'Login';
require_once($_SERVER['DOCUMENT_ROOT'] . '/kernel/pages/core/header.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/kernel/data/dbconfig.php');

$errors = array();

if (isset($_SESSION['logged'])) {
    header("Location: /?page=profile");
    exit;
}

$data = $_POST;

if (isset($data['login-submit'])) {
    $login = $data['login'];
    $password = $data['password'];

    if (!preg_match('|^[A-Z0-9]+$|i', $login)) {
        $errors[] = 'Некорректный логин';
    }

    if (preg_match('/[^0-9a-zA-Z\_]/', $password)) {
        $errors[] = 'Пароль имеет недопустимые символы';
    }

    echo array_shift($errors);

    if (empty($errors)) {
        $password_encrypt = md5(md5($password));
        $get_login_isset = mysqli_query($connect, "SELECT * FROM `users` WHERE `login`='" . $login . "' AND `password`='" . $password_encrypt . "' LIMIT 1");
        while($get_login_result = mysqli_fetch_array($get_login_isset)) {
            $login_correct = $get_login_result['login'];
            $password_decrypt = $get_login_result['password'];
        }

        if($login === $login_correct && $password_encrypt == $password_decrypt) {
            $_SESSION['logged'] = $login;
            header('Location: /game/lobby.php');
        } else {
            echo 'error';
        }

    }

}

?>

<div class="container">
    <form method="POST">
        <input type="text" placeholder="Логин" id="login" name="login"> <br>
        <input type="password" placeholder="Пароль" id="password" name="password"> <br>

        <button type="submit" name="login-submit">Авторизироваться</button>
    </form>
</div>

<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/kernel/pages/core/footer.php');
?>