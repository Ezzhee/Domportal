<?php
include 'db.php';
session_start();

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $username = $_POST['username'];
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE username='$username'");
    $user = $result->fetch_assoc();

    if($user && password_verify($password, $user['password'])){
        $_SESSION['user'] = $user;
        echo "✅ Добро пожаловать, " . $user['username'] . "! <a href='index.php'>На главную</a>";
    } else {
        echo "❌ Неверный логин или пароль.";
    }
}
?>

<h2>Вход</h2>
<form method="POST">
    <input name="username" placeholder="Логин" required><br>
    <input name="password" type="password" placeholder="Пароль" required><br>
    <button type="submit">Войти</button>
</form>
