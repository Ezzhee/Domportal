<?php
include 'db.php';
session_start();

if(!isset($_SESSION['user'])){
    die("Сначала <a href='login.php'>войдите</a>.");
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $title = $_POST['title'];
    $content = $_POST['content'];
    $author_id = $_SESSION['user']['id'];

    $conn->query("INSERT INTO posts (title, content, author_id) VALUES ('$title', '$content', '$author_id')");
    echo "✅ Новость добавлена! <a href='index.php'>На главную</a>";
}
?>

<h2>Добавить новость</h2>
<form method="POST">
    <input name="title" placeholder="Заголовок" required><br>
    <textarea name="content" placeholder="Текст новости" required></textarea><br>
    <button type="submit">Добавить</button>
</form>
