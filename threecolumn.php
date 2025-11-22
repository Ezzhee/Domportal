<?php
include 'db.php';
session_start();
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Полезное — ЖКХ Портал</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <link rel="stylesheet" href="assets/css/main.css" />
    <style>
        .admin-notice {
            background: #fff3cd;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
            border-left: 4px solid #ff9800;
        }
    </style>
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
                                
                                <?php if (isLoggedIn()): ?>
                                    <?php if (isAdmin()): ?>
                                        <a href="admin/index.php" style="color: #ff5722;">Админ</a>
                                    <?php endif; ?>
                                    <a href="logout.php">Выход (<?php echo escape(getCurrentUser()['username']); ?>)</a>
                                <?php else: ?>
                                    <a href="login.php">Вход</a>
                                    <a href="register.php">Регистрация</a>
                                <?php endif; ?>
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
                            <h2>💡 Полезные советы и объявления</h2>

                            <?php if (isAdmin()): ?>
                                <div class="admin-notice">
                                    <strong>🛠️ Управление материалами:</strong> 
                                    Перейдите в <a href="admin/helpful.php">админ-панель</a> для добавления и редактирования полезной информации.
                                </div>
                            <?php endif; ?>

                            <?php
                            $res = $conn->query("SELECT * FROM helpful_info ORDER BY created_at DESC");
                            if ($res && $res->num_rows > 0) {
                                while ($row = $res->fetch_assoc()) {
                                    echo "<article style='background: #f9f9f9; padding: 20px; margin: 15px 0; border-radius: 8px;'>";
                                    echo "<h3>" . escape($row['title']) . "</h3>";
                                    echo "<p>" . nl2br(escape($row['content'])) . "</p>";
                                    echo "<small style='color: #666;'>Добавлено: " . $row['created_at'] . "</small>";
                                    echo "</article>";
                                }
                            } else {
                                echo "<p>Пока нет опубликованных материалов.</p>";
                            }
                            ?>
                        </section>
                    </div>

                    <!-- Правая колонка -->
                    <div class="col-3 col-12-medium">
                        <section>
                            <h2>Последние пользователи</h2>
                            <ul class="small-image-list">
                            <?php
                            $users = $conn->query("SELECT username, role FROM users ORDER BY id DESC LIMIT 5");
                            if ($users) {
                                while ($u = $users->fetch_assoc()) {
                                    $username_html = escape($u['username']);
                                    $badge = $u['role'] === 'admin' ? ' 👑' : '';
                                    echo "<li><img src='images/pic1.jpg' alt='' class='left' /><p>{$username_html}{$badge}</p></li>";
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