<?php
include 'db.php';

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (username, password) VALUES ('$username', '$password')";
    if($conn->query($sql)){
        echo "✅ Регистрация успешна! <a href='login.php'>Войти</a>";
    } else {
        echo "❌ Ошибка: " . $conn->error;
    }
}
?>

<h2>Регистрация</h2>
<form method="POST">
    <input name="username" placeholder="Логин" required><br>
    <input name="password" type="password" placeholder="Пароль" required><br>
    <button type="submit">Зарегистрироваться</button>
</form>
