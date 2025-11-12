<?php
include 'db.php';   // подключаем базу данных
session_start();
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Новости ЖКХ Астаны</title>
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
									<a href="twocolumn1.php" class="current-page-item">Новости</a>
									<a href="twocolumn2.php">Статьи</a>
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

						<!-- Левая колонка — новости -->
						<div class="col-8 col-12-medium">
							<section>
								<h2>Последние новости</h2>
								<?php
								$result = $conn->query("SELECT * FROM posts ORDER BY created_at DESC");
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
									echo "<p>Пока нет опубликованных новостей.</p>";
								}
								?>
							</section>

							<!-- Форма для добавления новости -->
							<?php if (isset($_SESSION['user'])): ?>
							<section>
								<h2>Добавить новость</h2>
								<form method="POST" action="">
									<input type="text" name="title" placeholder="Заголовок" required><br>
									<textarea name="content" placeholder="Текст новости" required></textarea><br>
									<button type="submit" name="add_post">Опубликовать</button>
								</form>
							</section>
							<?php
							if (isset($_POST['add_post'])) {
								$title = $_POST['title'];
								$content = $_POST['content'];
								$author = $_SESSION['user']['username'] ?? 'Гость';
								$conn->query("INSERT INTO posts (title, content, author) VALUES ('$title','$content','$author')");
								echo "<p>✅ Новость добавлена! Обновите страницу.</p>";
							}
							?>
							<?php else: ?>
								<p><a href="login.php">Войдите</a>, чтобы добавить новость.</p>
							<?php endif; ?>
						</div>

						<!-- Правая колонка (сайдбар) -->
						<div class="col-4 col-12-medium">
							<section>
								<h2>Разделы портала</h2>
								<ul class="link-list">
									<li><a href="index.php">Главная страница</a></li>
									<li><a href="twocolumn1.php">Новости</a></li>
									<li><a href="twocolumn2.php">Статьи</a></li>
									<li><a href="onecolumn.php">Форум</a></li>
									<li><a href="threecolumn.php">Полезное</a></li>
								</ul>
							</section>

							<section>
								<h2>Последние пользователи</h2>
								<ul class="small-image-list">
								<?php
								$users = $conn->query("SELECT username FROM users ORDER BY id DESC LIMIT 5");
								while ($u = $users->fetch_assoc()) {
									echo "<li><img src='images/pic1.jpg' alt='' class='left'/><p>{$u['username']}</p></li>";
								}
								?>
								</ul>
							</section>
						</div>
					</div>
				</div>
			</div>

			<!-- Footer -->
			<div id="footer-wrapper">
				<div class="container">
					<div class="row">
						<div class="col-12">
							<div id="copyright">
								&copy; 2025 ЖКХ Портал. Дизайн: <a href="http://html5up.net">HTML5 UP</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Scripts -->
		<script src="assets/js/jquery.min.js"></script>
		<script src="assets/js/browser.min.js"></script>
		<script src="assets/js/breakpoints.min.js"></script>
		<script src="assets/js/util.js"></script>
		<script src="assets/js/main.js"></script>

	</body>
</html>
