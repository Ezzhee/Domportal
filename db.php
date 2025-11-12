<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "domportal";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}
?>