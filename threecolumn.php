<?php
// threecolumn.php
// подключение к БД и сессия
include 'db.php';
session_start();

// Обработка отправки формы (добавление полезной информации)
$form_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_info'])) {
    // простая валидация
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title === '' || $content === '') {
        $form_message = 'Заполните заголовок и текст.';
    } else {
        // подготовленное выражение (чтобы избежать SQL-инъекций)
        $stmt = $conn->prepare("INSERT INTO helpful_info (title, content) VALUES (?, ?)");
        if ($stmt) {
            $stmt->bind_param('ss', $title, $content);
            if ($stmt->execute()) {
                $form_message = '✅ Информация добавлена! Обновите страницу, чтобы увидеть запись.';
            } else {
                $form_message = 'Ошибка при сохранении: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            $form_message = 'Ошибка подготовки запроса: ' . $conn->error;
        }
    }
}
?>
<!DOCTYPE HTML>
<html>
<head>
	<title>Полезное — ЖКХ Портал</title>
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
								<a href="onecolumn.php">Форум</a>
								<a href="threecolumn.php" class="current-page-item">Полезное</a>
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

					<!-- Левая колонка -->
					<div class="col-3 col-12-medium">
						<section>
							<h2>Полезные ссылки</h2>
							<ul class="link-list">
								<li><a href="#">Оплата коммунальных услуг</a></li>
								<li><a href="#">Единый колл-центр 109</a></li>
								<li><a href="#">Официальный сайт акимата</a></li>
								<li><a href="#">Сервисы egov.kz</a></li>
								<li><a href="#">График вывоза мусора</a></li>
							</ul>
						</section>

						<section>
							<h2>Контакты ЖКХ</h2>
							<p><strong>Горячая линия:</strong> 8 (7172) 123-456<br>
							<strong>Email:</strong> info@zhkh-astana.kz</p>
						</section>
					</div>

					<!-- Средняя колонка -->
					<div class="col-6 col-12-medium imp-medium">
						<section>
							<h2>Полезные советы и объявления</h2>

							<?php
							// Выборка материалов из helpful_info
							$res = $conn->query("SELECT * FROM helpful_info ORDER BY created_at DESC");
							if ($res && $res->num_rows > 0) {
								while ($row = $res->fetch_assoc()) {
									echo "<article>";
									echo "<h3>" . htmlspecialchars($row['title']) . "</h3>";
									echo "<p>" . nl2br(htmlspecialchars($row['content'])) . "</p>";
									echo "<small>Добавлено: " . $row['created_at'] . "</small>";
									echo "<hr></article>";
								}
							} else {
								echo "<p>Пока нет опубликованных материалов.</p>";
							}
							?>

							<!-- Показ сообщения после отправки формы -->
							<?php if ($form_message !== ''): ?>
								<p><?php echo htmlspecialchars($form_message); ?></p>
							<?php endif; ?>

							<!-- Добавление новой записи (для авторизованных пользователей) -->
							<?php if (isset($_SESSION['user'])): ?>
								<section>
									<h3>Добавить материал</h3>
									<form method="POST">
										<input type="text" name="title" placeholder="Заголовок" required><br>
										<textarea name="content" placeholder="Описание / текст" required></textarea><br>
										<button type="submit" name="add_info">Добавить</button>
									</form>
								</section>
							<?php else: ?>
								<p><a href="login.php">Войдите</a>, чтобы добавить полезную информацию.</p>
							<?php endif; ?>
						</section>
					</div>

					<!-- Правая колонка -->
					<div class="col-3 col-12-medium">
						<section>
							<h2>Последние пользователи</h2>
							<ul class="small-image-list">
							<?php
							// безопасная выборка последних пользователей с лимитом
							$users = $conn->query("SELECT username FROM users ORDER BY id DESC LIMIT 5");
							if ($users) {
								while ($u = $users->fetch_assoc()) {
									$username_html = htmlspecialchars($u['username']);
									echo "<li><img src='images/pic1.jpg' alt='' class='left' /><p>{$username_html}</p></li>";
								}
							} else {
								echo "<li>Нет пользователей</li>";
							}
							?>
							</ul>
						</section>

						<section>
							<h2>О проекте</h2>
							<p>Этот раздел создан для обмена полезными материалами, советами и новостями, касающимися жизни в домах и микрорайонах Астаны.</p>
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
