<?php
include 'db.php';
session_start();
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Форум — ЖКХ Портал</title>
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
									<a href="twocolumn2.php">Статьи</a>
									<a href="onecolumn.php" class="current-page-item">Форум</a>
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
						<div class="col-12">

							<section>
								<h2>Форум ЖКХ — обсуждения и вопросы</h2>

								<!-- Вывод сообщений -->
								<?php
								$result = $conn->query("SELECT * FROM forum_posts ORDER BY created_at DESC");
								if ($result->num_rows > 0) {
									while ($row = $result->fetch_assoc()) {
										echo "<div class='post'>";
										echo "<h3>Сообщение от: {$row['username']}</h3>";
										echo "<p>{$row['message']}</p>";
										echo "<small>Отправлено: {$row['created_at']}</small>";
										echo "<hr></div>";
									}
								} else {
									echo "<p>На форуме пока нет сообщений.</p>";
								}
								?>
							</section>

							<!-- Добавление нового сообщения -->
							<section>
								<h2>Оставить сообщение</h2>

								<?php if (isset($_SESSION['user'])): ?>
								<form method="POST">
									<textarea name="message" placeholder="Ваше сообщение..." required></textarea><br>
									<button type="submit" name="send">Отправить</button>
								</form>

								<?php
								if (isset($_POST['send'])) {
									$message = $_POST['message'];
									$username = $_SESSION['user']['username'] ?? 'Гость';
									$conn->query("INSERT INTO forum_posts (username, message) VALUES ('$username', '$message')");
									echo "<p>✅ Сообщение опубликовано! Обновите страницу.</p>";
								}
								?>

								<?php else: ?>
									<p><a href="login.php">Войдите</a>, чтобы писать на форуме.</p>
								<?php endif; ?>
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
