<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "facemark";

$con = mysqli_connect($host, $username, $password, $database);

if (!$con) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}
