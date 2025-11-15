<?php
include '../db.php';
session_start();

if (!isAdmin()) {
    redirect('../index.php', 'Доступ запрещён');
}

$flash = '';
$edit_mode = false;
$edit_post = null;

// Обработка добавления/редактирования
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_post'])) {
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);
        $author_id = getCurrentUser()['id'];
        
        if (!empty($title) && !empty($content)) {
            $stmt = $conn->prepare("INSERT INTO posts (title, content, author_id, author) VALUES (?, ?, ?, ?)");
            $author_name = getCurrentUser()['username'];
            $stmt->bind_param("ssis", $title, $content, $author_id, $author_name);
            if ($stmt->execute()) {
                $flash = '✅ Новость добавлена!';
            }
            $stmt->close();
        }
    }
    
    if (isset($_POST['update_post'])) {
        $id = (int)$_POST['id'];
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);
        
        if (!empty($title) && !empty($content)) {
            $stmt = $conn->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ?");
            $stmt->bind_param("ssi", $title, $content, $id);
            if ($stmt->execute()) {
                $flash = '✅ Новость обновлена!';
            }
            $stmt->close();
        }
    }
}

// Обработка удаления
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM posts WHERE id = $id");
    $flash = '✅ Новость удалена!';
}

// Режим редактирования
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_post = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Получаем все новости
$posts = $conn->query("SELECT p.*, u.username FROM posts p LEFT JOIN users u ON p.author_id = u.id ORDER BY p.created_at DESC");
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Управление новостями — Админ-панель</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <link rel="stylesheet" href="../assets/css/main.css" />
    <style>
        .admin-form {
            background: #f9f9f9;
            padding: 25px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            font-family: inherit;
        }
        .form-group textarea {
            min-height: 150px;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            margin-right: 10px;
        }
        .btn-primary {
            background: #4CAF50;
            color: white;
        }
        .btn-primary:hover {
            background: #45a049;
        }
        .btn-cancel {
            background: #999;
            color: white;
        }
        .btn-cancel:hover {
            background: #777;
        }
        .post-list {
            margin-top: 30px;
        }
        .post-item {
            background: white;
            padding: 20px;
            margin: 15px 0;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .post-actions {
            margin-top: 15px;
        }
        .btn-small {
            padding: 8px 16px;
            font-size: 14px;
        }
        .btn-edit {
            background: #2196F3;
            color: white;
        }
        .btn-edit:hover {
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
            background: #4CAF50;
            color: white;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
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
                
                <a href="index.php" class="back-link">← Назад в админ-панель</a>
                
                <?php if ($flash): ?>
                    <div class="flash"><?php echo $flash; ?></div>
                <?php endif; ?>

                <h2>📰 Управление новостями</h2>

                <!-- Форма добавления/редактирования -->
                <div class="admin-form">
                    <h3><?php echo $edit_mode ? 'Редактировать новость' : 'Добавить новость'; ?></h3>
                    <form method="POST">
                        <?php if ($edit_mode): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_post['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label>Заголовок:</label>
                            <input type="text" name="title" 
                                   value="<?php echo $edit_mode ? escape($edit_post['title']) : ''; ?>" 
                                   placeholder="Введите заголовок новости" required>
                        </div>

                        <div class="form-group">
                            <label>Содержание:</label>
                            <textarea name="content" 
                                      placeholder="Введите текст новости" 
                                      required><?php echo $edit_mode ? escape($edit_post['content']) : ''; ?></textarea>
                        </div>

                        <?php if ($edit_mode): ?>
                            <button type="submit" name="update_post" class="btn btn-primary">💾 Сохранить изменения</button>
                            <a href="posts.php" class="btn btn-cancel">Отмена</a>
                        <?php else: ?>
                            <button type="submit" name="add_post" class="btn btn-primary">➕ Добавить новость</button>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Список новостей -->
                <div class="post-list">
                    <h3>Все новости (<?php echo $posts->num_rows; ?>)</h3>
                    <?php if ($posts->num_rows > 0): ?>
                        <?php while ($post = $posts->fetch_assoc()): ?>
                            <div class="post-item">
                                <h4><?php echo escape($post['title']); ?></h4>
                                <p><?php echo nl2br(escape(mb_substr($post['content'], 0, 200))); ?>...</p>
                                <small>
                                    Автор: <?php echo escape($post['username'] ?? $post['author']); ?> | 
                                    Дата: <?php echo $post['created_at']; ?>
                                </small>
                                <div class="post-actions">
                                    <a href="?edit=<?php echo $post['id']; ?>" class="btn btn-small btn-edit">✏️ Редактировать</a>
                                    <a href="?delete=<?php echo $post['id']; ?>" 
                                       class="btn btn-small btn-delete" 
                                       onclick="return confirm('Удалить эту новость?')">🗑️ Удалить</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>Новостей пока нет.</p>
                    <?php endif; ?>
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