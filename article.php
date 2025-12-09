<?php
include 'db.php';
session_start();

$article_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($article_id <= 0) {
    redirect('twocolumn2.php', 'Статья не найдена');
}

// Получаем статью
$stmt = $conn->prepare("
    SELECT a.*, u.username as author_name, u.role as author_role 
    FROM articles a 
    LEFT JOIN users u ON a.author_id = u.id 
    WHERE a.id = ?
");
$stmt->bind_param("i", $article_id);
$stmt->execute();
$result = $stmt->get_result();
$article = $result->fetch_assoc();
$stmt->close();

if (!$article) {
    redirect('twocolumn2.php', 'Статья не найдена');
}
?>
<!DOCTYPE HTML>
<html>
<head>
    <title><?php echo escape($article['title']); ?> — ЖКХ Портал</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <link rel="stylesheet" href="assets/css/main.css" />
    <style>
        .article-content {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .article-image {
            width: 100%;
            max-width: 800px;
            height: auto;
            border-radius: 8px;
            margin: 20px 0;
        }
        .article-meta {
            color: #666;
            font-size: 14px;
            margin: 20px 0;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 4px;
        }
        .admin-badge {
            background: #ff5722;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            margin-left: 5px;
        }
        .back-link {
            display: inline-block;
            margin: 20px 0;
            padding: 10px 20px;
            background: #2196F3;
            color: white;
            border-radius: 4px;
            text-decoration: none;
        }
        .back-link:hover {
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
                                <a href="twocolumn1.php">Новости</a>
                                <a href="twocolumn2.php" class="current-page-item">Статьи</a>
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
                <a href="twocolumn2.php" class="back-link">← Назад к статьям</a>

                <div class="article-content">
                    <h1><?php echo escape($article['title']); ?></h1>
                    
                    <div class="article-meta">
                        <strong>Автор:</strong> <?php echo escape($article['author_name'] ?? $article['author'] ?? 'Редакция'); ?>
                        <?php if ($article['author_role'] === 'admin'): ?>
                            <span class="admin-badge">АДМИН</span>
                        <?php endif; ?>
                        <br>
                        <strong>Дата публикации:</strong> <?php echo $article['created_at']; ?>
                    </div>

                    <?php if (!empty($article['image']) && file_exists($article['image'])): ?>
                        <img src="<?php echo escape($article['image']); ?>" alt="<?php echo escape($article['title']); ?>" class="article-image">
                    <?php endif; ?>

                    <div style="line-height: 1.8; font-size: 16px;">
                        <?php echo nl2br(escape($article['content'])); ?>
                    </div>
                </div>

                <a href="twocolumn2.php" class="back-link" style="margin-top: 30px;">← Назад к статьям</a>
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

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/browser.min.js"></script>
    <script src="assets/js/breakpoints.min.js"></script>
    <script src="assets/js/util.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>