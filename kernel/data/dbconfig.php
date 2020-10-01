<?php
// Connect to database
$host = "127.0.0.1"; // HOST
$database = "fortnite"; // THE NAME OF DATABASE
$user = "mysql"; // USERNAME
$password = "mysql"; // PASSWORD
$db_charset = 'utf8'; // utf8 or windows-1251. You must set utf8 not utf-8, basically it's not will be working.

$connect = mysqli_connect($host, $user, $password, $database)
    or die("Connection to database didn't established!" .mysqli_error() );
mysqli_set_charset($connect, $db_charset);
?>