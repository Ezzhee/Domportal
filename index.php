<?php
include 'db.php';
session_start();

// Получаем flash-сообщение, если есть
$flash = getFlashMessage();
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Новости ЖКХ Астаны</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <link rel="stylesheet" href="assets/css/main.css" />
    <style>
        .flash-message {
            background: #4CAF50;
            color: white;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            text-align: center;
        }
        .user-info {
            background: #f5f5f5;
            padding: 10px 15px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .admin-badge {
            background: #ff5722;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            margin-left: 5px;
        }
        .news-preview {
            background: #f9f9f9;
            padding: 15px;
            margin: 15px 0;
            border-radius: 8px;
            border-left: 4px solid #2196F3;
        }
        .news-preview h3 {
            margin-top: 0;
        }
        .news-preview h3 a {
            color: #2196F3;
            text-decoration: none;
        }
        .news-preview h3 a:hover {
            color: #1976D2;
            text-decoration: underline;
        }
        .news-preview-image {
            width: 100%;
            max-height: 200px;
            object-fit: cover;
            border-radius: 4px;
            margin-bottom: 10px;
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
                                <a href="index.php" class="current-page-item">Главная</a>
                                <a href="twocolumn1.php">Новости</a>
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

        <!-- Banner -->
        <div id="banner-wrapper">
            <div class="container">
                <div id="banner">
                    <h2>Добро пожаловать на портал ЖКХ</h2>
                    <span>Все новости, объявления и обсуждения в одном месте</span>
                </div>
            </div>
        </div>

        <!-- Main -->
        <div id="main">
            <div class="container">
                
                <?php if ($flash): ?>
                    <div class="flash-message">
                        <?php echo escape($flash); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isLoggedIn()): ?>
                    <div class="user-info">
                        👤 Вы вошли как: <strong><?php echo escape(getCurrentUser()['username']); ?></strong>
                        <?php if (isAdmin()): ?>
                            <span class="admin-badge">АДМИН/РЕДАКТОР</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="row main-row">
                    <div class="col-12">
                        <section>
                            <h2>Последние новости</h2>
                            <?php
                            // Выводим последние новости с информацией об авторе
                            $stmt = $conn->prepare("
                                SELECT p.*, u.username as author_name 
                                FROM posts p 
                                LEFT JOIN users u ON p.author_id = u.id 
                                ORDER BY p.created_at DESC 
                                LIMIT 5
                            ");
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<article class='news-preview'>";
                                    
                                    // Изображение
                                    if (!empty($row['image']) && file_exists($row['image'])) {
                                        echo "<a href='post.php?id={$row['id']}'>";
                                        echo "<img src='" . escape($row['image']) . "' alt='" . escape($row['title']) . "' class='news-preview-image'>";
                                        echo "</a>";
                                    }
                                    
                                    echo "<h3><a href='post.php?id={$row['id']}'>" . escape($row['title']) . "</a></h3>";
                                    
                                    // Превью текста
                                    $excerpt = mb_substr($row['content'], 0, 200);
                                    if (mb_strlen($row['content']) > 200) $excerpt .= '...';
                                    echo "<p>" . nl2br(escape($excerpt)) . "</p>";
                                    
                                    $authorName = $row['author_name'] ?? $row['author'] ?? 'Неизвестный';
                                    echo "<small>Автор: " . escape($authorName) . " | " . $row['created_at'] . " | ";
                                    echo "<a href='post.php?id={$row['id']}' style='color: #2196F3;'>Читать далее →</a></small>";
                                    
                                    echo "</article>";
                                }
                            } else {
                                echo "<p>Новостей пока нет.</p>";
                            }
                            $stmt->close();
                            ?>
                            <footer class="controls">
                                <a href="twocolumn1.php" class="button">Все новости</a>
                            </footer>
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