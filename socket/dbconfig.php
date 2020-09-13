<?php
// Connect to database
$host = "127.0.0.1"; // HOST
$database = "fortnite"; // THE NAME OF DATABASE
$user = "mysql"; // USERNAME
$password = "mysql"; // PASSWORD

$connect = mysqli_connect($host, $user, $password, $database)
or die("Connection to database didn't established!" .mysqli_error() );
?>