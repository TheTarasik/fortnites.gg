<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/kernel/data/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/kernel/data/dbconfig.php');
$login = $_SESSION['logged'];

    // Get user money
    $get_money = mysqli_query($connect, "SELECT `money` FROM `users` WHERE `login`='" . $login . "'");
    while ($get_money_result = mysqli_fetch_assoc($get_money)) {
        $user_money = $get_money_result['money'];
    }

$token_generate = md5(md5(rand(1000,9999).rand(1000,9999).rand(1000,9999)));
setcookie("XSRF_TOKEN", $token_generate, time() + 60 * 60 * 24, '/');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php if(isset($title)) { echo $title . ' ' . $id_room . " | " . $site_name ; } else { echo $site_name; }?></title>

    <link rel="shortcut icon" type="image/icon" href="<? echo $theme ?>/images/favicon.ico">
    <link rel="icon" type="image/icon" href="<? echo $theme ?>/images/favicon.ico">
    <link rel="apple-touch-icon-precomposed" href="<? echo $theme ?>/images/favicon.ico" />

    <link rel="stylesheet" href="<? echo $theme ?>/css/resptt.css">

    <script src="<? echo $theme ?>/js/misc.js"></script>
</head>
<body>

<nav class="navbar">
    <div class="container">
        <div class="navbar-wrapper">
            <div class="navbar-wrapper__item">
                <ul class="navbar-list">
                    <li class="navbar-list__item">
                        <div class="navbar-logo">
                            <img src="<? echo $theme ?>/images/favicon.ico"/>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="navbar-wrapper__item">
                <ul class="navbar-list">
                    <?
                    if (!isset($_SESSION['logged'])) {
                        echo '
                        <li class="navbar-list__item">
                        <a class="navbar-list__item-link" href="/?page=login" title="Авторизация">Авторизация</a>
                    </li>
                    <li class="navbar-list__item">
                    <a class="navbar-list__item-link" href="/?page=registration" title="Регистрация">
                        Регистрация
                    </a>
                    </li>
                        ';
                    } else {
                        echo '
                        <li class="navbar-list__item list-item__dropdown-info">
                         <span id="login">' . $login . '</span> 
                         <div class="navbar-list__item-userpick">
                        <img src="/uploads/';
                        $photo = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $login . '.jpg';
                        if (file_exists($photo)) {
                            echo $login . '.jpg';
                        } else {
                            echo 'default' . '.png';
                        }

                        echo '"
                            />
                        </div>
                        
                         <div class="dropdown-info__list">
                            <ul>
                                <li><a href="/?page=profile" title="Профиль">Профиль</a></li>
                                <li><a href="/?page=logout">Выход</a></li>
                            </ul>
                        </div>
                    </li>
                    <li class="navbar-list__item">
                        <span><a id="balance" href="/?page=addbalance" title="Баланс">
                          ' . $user_money . '
                        </a>₽</span>
                    </li>
                     <li class="navbar-list__item">
                        <a href="/?page=logout" title="Выход">Выход</a>
                    </li>
                        ';
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>

</nav>