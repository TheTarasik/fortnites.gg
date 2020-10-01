<?php
$title = 'Login';
require_once($_SERVER['DOCUMENT_ROOT'] . '/kernel/pages/core/header.php');

if (isset($_SESSION['logged'])) {
    header("Location: /?page=profile");
    exit;
}

function protectsqlinj($connect, $value) {
    return mysqli_real_escape_string($connect, $value);
}

$login = protectsqlinj($connect, $_POST['login']);
$password = protectsqlinj($connect, $_POST['password']);

$errors = array();

if (isset($_POST['login-submit'])) {
    if ($_COOKIE['XSRF_TOKEN'] != $_POST['xsrf_token']) {
        $errors[] = 'Некорректный XSRF-TOKEN';
    }

    if (!preg_match('|^[A-Z0-9]+$|i', $login)) {
        $errors[] = 'Некорректный логин';
    }

    if (preg_match('/[^0-9a-zA-Z\_]/', $password)) {
        $errors[] = 'Пароль имеет недопустимые символы';
    }

    if (empty($errors)) {
        $password_encrypt = md5(md5($password));
        $get_login_isset = mysqli_query($connect, "SELECT * FROM `users` WHERE `login`='" . $login . "' AND `password`='" . $password_encrypt . "' LIMIT 1");
        while ($get_login_result = mysqli_fetch_array($get_login_isset)) {
            $login_correct = $get_login_result['login'];
            $password_decrypt = $get_login_result['password'];
        }

        $logged = false;
        if ($login === $login_correct && $password_encrypt == $password_decrypt) {
            $logged = true;
        }

        if ($logged == true) {
            $_SESSION['logged'] = $login;
            header('Location: /game/lobby.php');
        } else {
            echo 'Неверный логин или пароль';
        }
    }

}

echo array_shift($errors);
?>

<div class="container">
    <form method="POST">
        <input type="hidden" name="xsrf_token" value="<? echo $token_generate ?>">
        <input type="text" placeholder="Логин" minlength="3" maxlength="16" id="login" name="login"> <br>
        <input type="password" placeholder="Пароль" minlength="6" maxlength="50" id="password" name="password"> <br>

        <button type="submit" name="login-submit">Авторизироваться</button>
    </form>
</div>

<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/kernel/pages/core/footer.php');
?>