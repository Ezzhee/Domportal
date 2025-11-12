<?php
include 'db.php';
session_start();
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Статьи — ЖКХ Портал</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
		<link rel="stylesheet" href="assets/css/main.css" />
	</head>
	<body>
		<div id="page-wrapper">

			<!-- Header -->
			<div id="header-wrapper">
				<div class="container">
					<div class="row">
						<div class="col-12">
							<header id="header">
								<h1><a href="index.php" id="logo">Новости ЖКХ Астаны</a></h1>
								<nav id="nav">
									<a href="index.php">Главная</a>
									<a href="twocolumn1.php">Новости</a>
									<a href="twocolumn2.php" class="current-page-item">Статьи</a>
									<a href="onecolumn.php">Форум</a>
									<a href="threecolumn.php">Полезное</a>
								</nav>
							</header>
						</div>
					</div>
				</div>
			</div>

			<!-- Main -->
			<div id="main">
				<div class="container">
					<div class="row main-row">

						<!-- Левая колонка — список статей -->
						<div class="col-8 col-12-medium imp-medium">
							<section>
								<h2>Публикации и статьи</h2>
								<?php
								$result = $conn->query("SELECT * FROM articles ORDER BY created_at DESC");
								if ($result->num_rows > 0) {
									while ($row = $result->fetch_assoc()) {
										echo "<article>";
										echo "<h3>{$row['title']}</h3>";
										echo "<p>{$row['content']}</p>";
										echo "<small>Автор: {$row['author']} | {$row['created_at']}</small>";
										echo "<hr>";
										echo "</article>";
									}
								} else {
									echo "<p>Пока нет опубликованных статей.</p>";
								}
								?>
							</section>

							<!-- Добавление статьи -->
							<?php if (isset($_SESSION['user'])): ?>
							<section>
								<h2>Добавить статью</h2>
								<form method="POST">
									<input type="text" name="title" placeholder="Заголовок" required><br>
									<textarea name="content" placeholder="Текст статьи" required></textarea><br>
									<button type="submit" name="add_article">Опубликовать</button>
								</form>
							</section>
							<?php
							if (isset($_POST['add_article'])) {
								$title = $_POST['title'];
								$content = $_POST['content'];
								$author = $_SESSION['user']['username'] ?? 'Гость';
								$conn->query("INSERT INTO articles (title, content, author) VALUES ('$title', '$content', '$author')");
								echo "<p>✅ Статья добавлена! Обновите страницу.</p>";
							}
							?>
							<?php else: ?>
								<p><a href="login.php">Войдите</a>, чтобы опубликовать статью.</p>
							<?php endif; ?>
						</div>

						<!-- Правая колонка — сайдбар -->
						<div class="col-4 col-12-medium">
							<section>
								<h
