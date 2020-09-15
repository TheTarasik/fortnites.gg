<?php
$title = 'Registration';
require_once($_SERVER['DOCUMENT_ROOT'] . '/kernel/pages/core/header.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/kernel/data/dbconfig.php');

$data = $_POST;
$ip = $_SERVER['SERVER_ADDR'];

$errors = array();

if (isset($_SESSION['logged'])) {
    header("Location: /?page=profile");
    exit;
}

if (isset($data['registration-submit'])) {
    $login = $data['login'];
    $email = $data['email'];
    $password = $data['password'];
    $password_repeat = $data['password-repeat'];

    if (strlen($login) < 4) {
        $errors[] = 'Логин меньше 4';
    }

    if (strlen($login) > 15) {
        $errors[] = 'Логин больше 15';
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

    echo array_shift($errors);

    if (empty($errors)) {
        $_SESSION['logged'] = $login;

        $password_encrypt = md5(md5($password));
        $token_generate = md5(md5(rand(1000,9999).rand(1000,9999).rand(1000,9999)));
        if ($login) {
            setcookie( "token", $token_generate, time()+3600, "/" );
            $register = mysqli_query($connect, "INSERT INTO `users` (`id`, `login`, `email`, `password`, `ip`, `token`) VALUES (NULL, '" . $login . "', '" . $email . "', '" . $password_encrypt . "', '" . $ip . "', '" . $token_generate . "')");
            header('Location: /game/lobby.php');
        }
    }

}

?>

<div class="container">
    <form method="POST">
        <input type="text" placeholder="Логин" id="login" name="login"> <br>
        <input type="email" placeholder="Email" id="email" name="email"> <br>
        <input type="password" placeholder="Пароль" id="password" name="password"> <br>
        <input type="password" placeholder="Повторите пароль" id="password-repeat" name="password-repeat"> <br>

        <button type="submit" name="registration-submit">Зарегистрироваться</button>
    </form>
</div>

<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/kernel/pages/core/footer.php');
?>