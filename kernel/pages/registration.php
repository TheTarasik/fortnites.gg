<?php
$title = 'Registration';
require_once($_SERVER['DOCUMENT_ROOT'] . '/kernel/pages/core/header.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/kernel/data/dbconfig.php');

$ip = $_SERVER['SERVER_ADDR'];

$errors = array();

if (isset($_SESSION['logged'])) {
    header("Location: /?page=profile");
    exit;
}

function protectsqlinj($connect, $value) {
    return mysqli_real_escape_string($connect, $value);
}

$login = protectsqlinj($connect, $_POST['login']);
$email = protectsqlinj($connect,$_POST['email']);
$eg_login = protectsqlinj($connect,$_POST['eg_login']);
$password = protectsqlinj($connect, $_POST['password']);
$password_repeat = $_POST['password-repeat'];

if (isset($_POST['registration-submit'])) {
    if ($_COOKIE['XSRF_TOKEN'] != $_POST['xsrf_token']) {
        $errors[] = 'Некорректный XSRF-TOKEN';
    }

    if (strlen($login) < 3) {
        $errors[] = 'Логин меньше 3';
    }

    if (strlen($login) > 16) {
        $errors[] = 'Логин больше 16';
    }

    if (strlen($eg_login) < 3) {
        $errors[] = 'Логин EpicGames меньше 3';
    }

    if (strlen($eg_login) > 16) {
        $errors[] = 'Логин EpicGames больше 16';
    }

    if (strlen($password) < 6 || strlen($password) > 50) {
        $errors[] = 'Макс длина пароля 50 минимальная 6';
    }

    if (trim($login && $password && $password_repeat && $email) == '') {
        $errors[] = 'Одно или несколько полей не заполнены';
    }

    if ($password != $password_repeat) {
        $errors[] = 'Пароли не совпадают';
    }

    if (!preg_match('|^[A-Z0-9]+$|i', $login)) {
        $errors[] = 'Некорректный логин';
    }

    if (!preg_match('|^[A-Z0-9]+$|i', $eg_login)) {
        $errors[] = 'Некорректный логин EpicGames';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Некорректный Email';
    }

    if (preg_match('/[^0-9a-zA-Z\_]/', $password)) {
        $errors[] = 'Пароль имеет недопустимые символы';
    }

    if (preg_match('/[^0-9a-zA-Z\_]/', $password_repeat)) {
        $errors[] = 'Пароль имеет недопустимые символы';
    }

    $get_isset_user = mysqli_query($connect, "SELECT `login` FROM `users` WHERE `login`='" . $login . "'");
    while ($get_isset_user_result = mysqli_fetch_array($get_isset_user)) {
        if ($get_isset_user_result['login'] == $login) {
            $errors[] = 'Этот логин уже зарегистрирован';
        }
    }

    $get_isset_email = mysqli_query($connect, "SELECT `email` FROM `users` WHERE `email`='" . $email . "'");
    while ($get_isset_email_result = mysqli_fetch_array($get_isset_email)) {
        if ($get_isset_email_result['email'] == $email) {
            $errors[] = 'Этот Email уже зарегистрирован';
        }
    }

    if (empty($errors)) {
        $_SESSION['logged'] = $login;

        $password_encrypt = md5(md5($password));
        $token_generate = md5(md5(rand(1000,9999).rand(1000,9999).rand(1000,9999)));
        if ($login) {
            setcookie( "token", $token_generate, time()+3600, "/");
            $register = mysqli_query($connect, "INSERT INTO `users` (`id`, `login`, `fortnite_name`, `email`, `password`, `ip`, `token`) VALUES (NULL, '" . $login . "', '" . $eg_login . "', '" . $email . "', '" . $password_encrypt . "', '" . $ip . "', '" . $token_generate . "')");
            header('Location: /game/lobby.php');
        }
    }

}

echo array_shift($errors);
?>

    <div class="container">
        <form method="POST">
            <input type="hidden" name="xsrf_token" value="<? echo $token_generate ?>">
            <input type="text" placeholder="Логин" minlength="3" maxlength="16" id="login" name="login" value="<? echo $login ?>"> <br>
            <input type="email" placeholder="Email" id="email" name="email" value="<? echo $email ?>"> <br>
            <input type="text" placeholder="Логин в EpicGames" minlength="3" maxlength="16" id="eg_login" name="eg_login" value="<? echo $eg_login ?>"> <br>
            <input type="password" placeholder="Пароль" minlength="6" maxlength="50" id="password" name="password"> <br>
            <input type="password" placeholder="Повторите пароль" minlength="6" maxlength="50" id="password-repeat" name="password-repeat"> <br>

            <button type="submit" name="registration-submit">Зарегистрироваться</button>
        </form>
    </div>

<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/kernel/pages/core/footer.php');
?>