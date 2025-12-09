<?php
include 'db.php';
session_start();

$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($post_id <= 0) {
    redirect('twocolumn1.php', 'Новость не найдена');
}

// Получаем новость
$stmt = $conn->prepare("
    SELECT p.*, u.username as author_name, u.role as author_role 
    FROM posts p 
    LEFT JOIN users u ON p.author_id = u.id 
    WHERE p.id = ?
");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();
$stmt->close();

if (!$post) {
    redirect('twocolumn1.php', 'Новость не найдена');
}
?>
<!DOCTYPE HTML>
<html>
<head>
    <title><?php echo escape($post['title']); ?> — ЖКХ Портал</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <link rel="stylesheet" href="assets/css/main.css" />
    <style>
        .post-content {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .post-image {
            width: 100%;
            max-width: 800px;
            height: auto;
            border-radius: 8px;
            margin: 20px 0;
        }
        .post-meta {
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
                <a href="twocolumn1.php" class="back-link">← Назад к новостям</a>

                <div class="post-content">
                    <h1><?php echo escape($post['title']); ?></h1>
                    
                    <div class="post-meta">
                        <strong>Автор:</strong> <?php echo escape($post['author_name'] ?? $post['author'] ?? 'Редакция'); ?>
                        <?php if ($post['author_role'] === 'admin'): ?>
                            <span class="admin-badge">АДМИН</span>
                        <?php endif; ?>
                        <br>
                        <strong>Дата публикации:</strong> <?php echo $post['created_at']; ?>
                    </div>

                    <?php if (!empty($post['image']) && file_exists($post['image'])): ?>
                        <img src="<?php echo escape($post['image']); ?>" alt="<?php echo escape($post['title']); ?>" class="post-image">
                    <?php endif; ?>

                    <div style="line-height: 1.8; font-size: 16px;">
                        <?php echo nl2br(escape($post['content'])); ?>
                    </div>
                </div>

                <a href="twocolumn1.php" class="back-link" style="margin-top: 30px;">← Назад к новостям</a>
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