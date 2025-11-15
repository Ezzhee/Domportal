<?php
include '../db.php';
session_start();

// Только для админов
if (!isAdmin()) {
    redirect('../index.php', 'Доступ запрещён');
}

// Получаем статистику
$stats = [];

// Количество пользователей
$result = $conn->query("SELECT COUNT(*) as count FROM users");
$stats['users'] = $result->fetch_assoc()['count'];

// Количество новостей
$result = $conn->query("SELECT COUNT(*) as count FROM posts");
$stats['posts'] = $result->fetch_assoc()['count'];

// Количество статей
$result = $conn->query("SELECT COUNT(*) as count FROM articles");
$stats['articles'] = $result->fetch_assoc()['count'];

// Количество тем на форуме
$result = $conn->query("SELECT COUNT(*) as count FROM forum_topics");
$stats['topics'] = $result->fetch_assoc()['count'];

// Количество полезной информации
$result = $conn->query("SELECT COUNT(*) as count FROM helpful_info");
$stats['helpful'] = $result->fetch_assoc()['count'];
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Админ-панель — ЖКХ Портал</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <link rel="stylesheet" href="../assets/css/main.css" />
    <style>
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            border-left: 4px solid #667eea;
        }
        .stat-number {
            font-size: 42px;
            font-weight: bold;
            color: #667eea;
            margin: 10px 0;
        }
        .stat-label {
            color: #666;
            font-size: 16px;
        }
        .admin-menu {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .menu-item {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            text-decoration: none;
            color: #333;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .menu-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        .menu-item h3 {
            color: #667eea;
            margin: 15px 0 10px 0;
        }
        .menu-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .back-link {
            display: inline-block;
            margin: 20px 0;
            padding: 10px 20px;
            background: #999;
            color: white;
            border-radius: 4px;
            text-decoration: none;
        }
        .back-link:hover {
            background: #777;
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
                            <h1><a href="../index.php" id="logo">Новости ЖКХ Астаны</a></h1>
                            <nav id="nav">
                                <a href="../index.php">Главная</a>
                                <a href="index.php" style="color: #ff5722;">Админ-панель</a>
                                <a href="../logout.php">Выход (<?php echo escape(getCurrentUser()['username']); ?>)</a>
                            </nav>
                        </header>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main -->
        <div id="main">
            <div class="container">
                
                <div class="admin-header">
                    <h1>🛠️ Панель управления</h1>
                    <p>Добро пожаловать, <?php echo escape(getCurrentUser()['username']); ?>! Здесь вы можете управлять всем контентом портала.</p>
                </div>

                <!-- Статистика -->
                <h2>📊 Статистика портала</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['users']; ?></div>
                        <div class="stat-label">👥 Пользователей</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['posts']; ?></div>
                        <div class="stat-label">📰 Новостей</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['articles']; ?></div>
                        <div class="stat-label">📝 Статей</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['topics']; ?></div>
                        <div class="stat-label">💬 Тем на форуме</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['helpful']; ?></div>
                        <div class="stat-label">💡 Полезных материалов</div>
                    </div>
                </div>

                <!-- Меню управления -->
                <h2>⚙️ Управление контентом</h2>
                <div class="admin-menu">
                    
                    <a href="posts.php" class="menu-item">
                        <div class="menu-icon">📰</div>
                        <h3>Новости</h3>
                        <p>Создание, редактирование и удаление новостей</p>
                    </a>

                    <a href="articles.php" class="menu-item">
                        <div class="menu-icon">📝</div>
                        <h3>Статьи</h3>
                        <p>Управление статьями и публикациями</p>
                    </a>

                    <a href="helpful.php" class="menu-item">
                        <div class="menu-icon">💡</div>
                        <h3>Полезное</h3>
                        <p>Советы и объявления</p>
                    </a>

                    <a href="../onecolumn.php" class="menu-item">
                        <div class="menu-icon">💬</div>
                        <h3>Форум</h3>
                        <p>Модерация форума (закрепление, закрытие тем)</p>
                    </a>

                    <a href="users.php" class="menu-item">
                        <div class="menu-icon">👥</div>
                        <h3>Пользователи</h3>
                        <p>Управление пользователями и правами</p>
                    </a>

                </div>

                <a href="../index.php" class="back-link">← Вернуться на сайт</a>

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
    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/browser.min.js"></script>
    <script src="../assets/js/breakpoints.min.js"></script>
    <script src="../assets/js/util.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>