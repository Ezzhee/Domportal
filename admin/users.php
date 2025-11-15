<?php
include '../db.php';
session_start();

if (!isAdmin()) {
    redirect('../index.php', 'Доступ запрещён');
}

$flash = '';

// Изменение роли пользователя
if (isset($_GET['toggle_role'])) {
    $user_id = (int)$_GET['toggle_role'];
    $current_user_id = getCurrentUser()['id'];
    
    // Нельзя изменить свою роль
    if ($user_id !== $current_user_id) {
        $conn->query("UPDATE users SET role = IF(role = 'admin', 'user', 'admin') WHERE id = $user_id");
        $flash = '✅ Роль пользователя изменена!';
    } else {
        $flash = '❌ Нельзя изменить свою собственную роль!';
    }
}

// Удаление пользователя
if (isset($_GET['delete'])) {
    $user_id = (int)$_GET['delete'];
    $current_user_id = getCurrentUser()['id'];
    
    // Нельзя удалить себя
    if ($user_id !== $current_user_id) {
        // Удаляем все связанные данные пользователя
        $conn->query("DELETE FROM forum_posts WHERE user_id = $user_id");
        $conn->query("DELETE FROM forum_topics WHERE author_id = $user_id");
        $conn->query("DELETE FROM forum_attachments WHERE user_id = $user_id");
        $conn->query("UPDATE posts SET author_id = NULL WHERE author_id = $user_id");
        $conn->query("UPDATE articles SET author_id = NULL WHERE author_id = $user_id");
        $conn->query("DELETE FROM users WHERE id = $user_id");
        $flash = '✅ Пользователь удалён!';
    } else {
        $flash = '❌ Нельзя удалить свой аккаунт!';
    }
}

// Получаем всех пользователей с статистикой
$users = $conn->query("
    SELECT 
        u.*,
        (SELECT COUNT(*) FROM posts WHERE author_id = u.id) as posts_count,
        (SELECT COUNT(*) FROM articles WHERE author_id = u.id) as articles_count,
        (SELECT COUNT(*) FROM forum_topics WHERE author_id = u.id) as topics_count,
        (SELECT COUNT(*) FROM forum_posts WHERE user_id = u.id) as replies_count
    FROM users u
    ORDER BY u.id ASC
");
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Управление пользователями — Админ-панель</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <link rel="stylesheet" href="../assets/css/main.css" />
    <style>
        .user-table {
            width: 100%;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin: 20px 0;
        }
        .user-table table {
            width: 100%;
            border-collapse: collapse;
        }
        .user-table th {
            background: #667eea;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: bold;
        }
        .user-table td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        .user-table tr:hover {
            background: #f9f9f9;
        }
        .role-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .role-admin {
            background: #ff5722;
            color: white;
        }
        .role-user {
            background: #4CAF50;
            color: white;
        }
        .user-stats {
            font-size: 13px;
            color: #666;
        }
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            text-decoration: none;
            display: inline-block;
            margin: 2px;
        }
        .btn-role {
            background: #2196F3;
            color: white;
        }
        .btn-role:hover {
            background: #1976D2;
        }
        .btn-delete {
            background: #f44336;
            color: white;
        }
        .btn-delete:hover {
            background: #d32f2f;
        }
        .flash {
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .flash-success {
            background: #4CAF50;
            color: white;
        }
        .flash-error {
            background: #f44336;
            color: white;
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
        .info-box {
            background: #fff3cd;
            padding: 15px;
            border-left: 4px solid #ff9800;
            border-radius: 4px;
            margin: 20px 0;
        }
        @media (max-width: 768px) {
            .user-table {
                overflow-x: auto;
            }
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
                
                <a href="index.php" class="back-link">← Назад в админ-панель</a>
                
                <?php if ($flash): ?>
                    <div class="flash <?php echo strpos($flash, '✅') !== false ? 'flash-success' : 'flash-error'; ?>">
                        <?php echo $flash; ?>
                    </div>
                <?php endif; ?>

                <h2>👥 Управление пользователями</h2>

                <div class="info-box">
                    <strong>ℹ️ Информация:</strong> Админы/редакторы могут создавать и редактировать контент. 
                    Обычные пользователи могут только участвовать в форуме.
                </div>

                <!-- Таблица пользователей -->
                <div class="user-table">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Логин</th>
                                <th>Роль</th>
                                <th>Активность</th>
                                <th>Регистрация</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = $users->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td>
                                        <strong><?php echo escape($user['username']); ?></strong>
                                        <?php if ($user['id'] == getCurrentUser()['id']): ?>
                                            <span style="color: #999; font-size: 12px;">(вы)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="role-badge role-<?php echo $user['role']; ?>">
                                            <?php echo $user['role'] === 'admin' ? '👑 АДМИН' : '👤 Пользователь'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="user-stats">
                                            📰 Новостей: <?php echo $user['posts_count']; ?><br>
                                            📝 Статей: <?php echo $user['articles_count']; ?><br>
                                            💬 Тем: <?php echo $user['topics_count']; ?> / Ответов: <?php echo $user['replies_count']; ?>
                                        </div>
                                    </td>
                                    <td style="font-size: 13px; color: #666;">
                                        <?php echo date('d.m.Y', strtotime($user['created_at'])); ?>
                                    </td>
                                    <td>
                                        <?php if ($user['id'] !== getCurrentUser()['id']): ?>
                                            <a href="?toggle_role=<?php echo $user['id']; ?>" 
                                               class="btn btn-role"
                                               onclick="return confirm('Изменить роль пользователя?')">
                                                <?php echo $user['role'] === 'admin' ? '⬇️ Сделать User' : '⬆️ Сделать Admin'; ?>
                                            </a>
                                            <a href="?delete=<?php echo $user['id']; ?>" 
                                               class="btn btn-delete"
                                               onclick="return confirm('Удалить пользователя и весь его контент?')">
                                                🗑️ Удалить
                                            </a>
                                        <?php else: ?>
                                            <span style="color: #999; font-size: 13px;">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 30px; padding: 20px; background: #f9f9f9; border-radius: 8px;">
                    <h3>📊 Общая статистика</h3>
                    <p><strong>Всего пользователей:</strong> <?php echo $users->num_rows; ?></p>
                    <?php
                    $admins = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'")->fetch_assoc()['count'];
                    $regular_users = $users->num_rows - $admins;
                    ?>
                    <p><strong>Админов/редакторов:</strong> <?php echo $admins; ?></p>
                    <p><strong>Обычных пользователей:</strong> <?php echo $regular_users; ?></p>
                </div>

            </div>
        </div>

        <!-- Footer -->
        <div id="footer-wrapper">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div id="copyright">
                            &copy; 2025 ЖКХ Портал
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/browser.min.js"></script>
    <script src="../assets/js/breakpoints.min.js"></script>
    <script src="../assets/js/util.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>