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
    <style>
        .topic-item {
            background: #f9f9f9;
            padding: 15px;
            margin: 10px 0;
            border-left: 4px solid #2196F3;
            border-radius: 4px;
        }
        .topic-item.pinned {
            background: #fff3cd;
            border-left-color: #ff9800;
        }
        .topic-item.closed {
            opacity: 0.6;
            border-left-color: #999;
        }
        .topic-meta {
            color: #666;
            font-size: 14px;
            margin-top: 10px;
        }
        .topic-stats {
            display: inline-block;
            margin-right: 15px;
        }
        .badge {
            background: #2196F3;
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 12px;
            margin-left: 5px;
        }
        .badge.pinned {
            background: #ff9800;
        }
        .badge.closed {
            background: #999;
        }
        .new-topic-btn {
            background: #4CAF50;
            color: white;
            padding: 12px 24px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            margin: 20px 0;
        }
        .new-topic-btn:hover {
            background: #45a049;
        }
        .admin-badge {
            background: #ff5722;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 11px;
            margin-left: 5px;
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
                                <a href="onecolumn.php" class="current-page-item">Форум</a>
                                <a href="threecolumn.php">Полезное</a>
                                
                                <?php if (isLoggedIn()): ?>
                                    <?php if (isAdmin()): ?>
                                        <a href="admin/index.php" style="color: #ff5722;">Админ-панель</a>
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
                    <div class="col-12">

                        <section>
                            <h2>Форум ЖКХ — обсуждения и вопросы</h2>

                            <?php if (isLoggedIn()): ?>
                                <a href="forum_new_topic.php" class="new-topic-btn">➕ Создать новую тему</a>
                            <?php else: ?>
                                <p><a href="login.php">Войдите</a>, чтобы создать тему на форуме.</p>
                            <?php endif; ?>

                            <hr>

                            <!-- Список тем -->
                            <?php
                            // Получаем темы с информацией об авторе
                            $stmt = $conn->prepare("
                                SELECT 
                                    t.*,
                                    u.username as author_name,
                                    u.role as author_role,
                                    (SELECT COUNT(*) FROM forum_posts WHERE topic_id = t.id) as replies_count
                                FROM forum_topics t
                                LEFT JOIN users u ON t.author_id = u.id
                                ORDER BY t.is_pinned DESC, t.updated_at DESC
                            ");
                            $stmt->execute();
                            $result = $stmt->get_result();

                            if ($result->num_rows > 0) {
                                while ($topic = $result->fetch_assoc()) {
                                    $cssClass = 'topic-item';
                                    if ($topic['is_pinned']) $cssClass .= ' pinned';
                                    if ($topic['is_closed']) $cssClass .= ' closed';
                                    
                                    echo "<div class='$cssClass'>";
                                    echo "<h3>";
                                    echo "<a href='forum_topic.php?id={$topic['id']}'>" . escape($topic['title']) . "</a>";
                                    
                                    if ($topic['is_pinned']) {
                                        echo " <span class='badge pinned'>📌 Закреплено</span>";
                                    }
                                    if ($topic['is_closed']) {
                                        echo " <span class='badge closed'>🔒 Закрыто</span>";
                                    }
                                    echo "</h3>";
                                    
                                    // Превью контента (первые 200 символов)
                                    $preview = mb_substr($topic['content'], 0, 200);
                                    if (mb_strlen($topic['content']) > 200) $preview .= '...';
                                    echo "<p>" . nl2br(escape($preview)) . "</p>";
                                    
                                    echo "<div class='topic-meta'>";
                                    echo "<span class='topic-stats'>💬 Ответов: {$topic['replies_count']}</span>";
                                    echo "<span class='topic-stats'>👁️ Просмотров: {$topic['views']}</span>";
                                    echo "<span>Автор: <strong>" . escape($topic['author_name']) . "</strong>";
                                    
                                    if ($topic['author_role'] === 'admin') {
                                        echo " <span class='admin-badge'>АДМИН</span>";
                                    }
                                    
                                    echo " | " . $topic['created_at'] . "</span>";
                                    echo "</div>";
                                    
                                    echo "</div>";
                                }
                            } else {
                                echo "<p>На форуме пока нет тем. Будьте первым, кто создаст обсуждение!</p>";
                            }
                            $stmt->close();
                            ?>
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