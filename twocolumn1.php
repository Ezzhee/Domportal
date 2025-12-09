<?php
include 'db.php';
session_start();
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Новости ЖКХ Астаны</title>
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
        .news-item {
            background: #f9f9f9;
            padding: 20px;
            margin: 15px 0;
            border-radius: 8px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .news-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .news-image {
            width: 100%;
            max-height: 300px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .news-title {
            color: #2196F3;
            text-decoration: none;
            font-size: 20px;
            font-weight: bold;
            display: block;
            margin-bottom: 10px;
        }
        .news-title:hover {
            color: #1976D2;
            text-decoration: underline;
        }
        .news-excerpt {
            color: #333;
            line-height: 1.6;
            margin: 10px 0;
        }
        .read-more {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 16px;
            background: #2196F3;
            color: white;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
        }
        .read-more:hover {
            background: #1976D2;
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
                                <a href="twocolumn1.php" class="current-page-item">Новости</a>
                                <a href="twocolumn2.php">Статьи</a>
                                <a href="onecolumn.php">Форум</a>
                                <a href="threecolumn.php">Полезное</a>
                                
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

                    <!-- Левая колонка — новости -->
                    <div class="col-8 col-12-medium">
                        <section>
                            <h2>📰 Последние новости</h2>
                            
                            <?php if (isAdmin()): ?>
                                <div class="admin-notice">
                                    <strong>🛠️ Управление новостями:</strong> 
                                    Перейдите в <a href="admin/posts.php">админ-панель</a> для добавления и редактирования новостей.
                                </div>
                            <?php endif; ?>
                            
                            <?php
                            $stmt = $conn->prepare("
                                SELECT p.*, u.username as author_name 
                                FROM posts p 
                                LEFT JOIN users u ON p.author_id = u.id 
                                ORDER BY p.created_at DESC
                            ");
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<article class='news-item'>";
                                    
                                    // Изображение
                                    if (!empty($row['image']) && file_exists($row['image'])) {
                                        echo "<a href='post.php?id={$row['id']}'>";
                                        echo "<img src='" . escape($row['image']) . "' alt='" . escape($row['title']) . "' class='news-image'>";
                                        echo "</a>";
                                    }
                                    
                                    // Заголовок
                                    echo "<a href='post.php?id={$row['id']}' class='news-title'>" . escape($row['title']) . "</a>";
                                    
                                    // Превью текста
                                    $excerpt = mb_substr($row['content'], 0, 250);
                                    if (mb_strlen($row['content']) > 250) $excerpt .= '...';
                                    echo "<div class='news-excerpt'>" . nl2br(escape($excerpt)) . "</div>";
                                    
                                    // Метаданные
                                    $authorName = $row['author_name'] ?? $row['author'] ?? 'Редакция';
                                    echo "<small style='color: #666;'>Автор: " . escape($authorName) . " | " . $row['created_at'] . "</small>";
                                    
                                    // Кнопка "Читать далее"
                                    echo "<br><a href='post.php?id={$row['id']}' class='read-more'>Читать далее →</a>";
                                    
                                    echo "</article>";
                                }
                            } else {
                                echo "<p>Пока нет опубликованных новостей.</p>";
                            }
                            $stmt->close();
                            ?>
                        </section>
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
                            $users = $conn->query("SELECT username, role FROM users ORDER BY id DESC LIMIT 5");
                            if ($users) {
                                while ($u = $users->fetch_assoc()) {
                                    $badge = $u['role'] === 'admin' ? ' 👑' : '';
                                    echo "<li><img src='images/pic1.jpg' alt='' class='left'/><p>" . escape($u['username']) . $badge . "</p></li>";
                                }
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