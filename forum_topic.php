<?php
include 'db.php';
session_start();

// Получаем ID темы
$topic_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($topic_id <= 0) {
    redirect('onecolumn.php', 'Тема не найдена');
}

// Увеличиваем счётчик просмотров
$conn->query("UPDATE forum_topics SET views = views + 1 WHERE id = $topic_id");

// Получаем информацию о теме
$stmt = $conn->prepare("
    SELECT t.*, u.username as author_name, u.role as author_role
    FROM forum_topics t
    LEFT JOIN users u ON t.author_id = u.id
    WHERE t.id = ?
");
$stmt->bind_param("i", $topic_id);
$stmt->execute();
$result = $stmt->get_result();
$topic = $result->fetch_assoc();
$stmt->close();

if (!$topic) {
    redirect('onecolumn.php', 'Тема не найдена');
}

$upload_error = '';

// Обработка добавления ответа с файлами
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_reply']) && isLoggedIn()) {
    $content = trim($_POST['content']);
    $user_id = getCurrentUser()['id'];
    
    if (!empty($content)) {
        // Создаём ответ
        $stmt = $conn->prepare("INSERT INTO forum_posts (topic_id, user_id, content) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $topic_id, $user_id, $content);
        
        if ($stmt->execute()) {
            $post_id = $conn->insert_id;
            
            // Обрабатываем загрузку файлов
            if (isset($_FILES['attachments']) && !empty($_FILES['attachments']['name'][0])) {
                $upload_dir = 'uploads/forum/';
                
                // Создаём папку, если её нет
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                $max_size = 5 * 1024 * 1024; // 5 MB
                
                foreach ($_FILES['attachments']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['attachments']['error'][$key] === UPLOAD_ERR_OK) {
                        $file_size = $_FILES['attachments']['size'][$key];
                        $file_type = $_FILES['attachments']['type'][$key];
                        $original_name = basename($_FILES['attachments']['name'][$key]);
                        
                        // Проверка типа и размера
                        if (!in_array($file_type, $allowed_types)) {
                            $upload_error .= "Файл $original_name имеет недопустимый тип. ";
                            continue;
                        }
                        
                        if ($file_size > $max_size) {
                            $upload_error .= "Файл $original_name слишком большой (макс. 5 МБ). ";
                            continue;
                        }
                        
                        // Генерируем безопасное имя файла
                        $file_ext = pathinfo($original_name, PATHINFO_EXTENSION);
                        $safe_filename = uniqid() . '_' . time() . '.' . $file_ext;
                        $filepath = $upload_dir . $safe_filename;
                        
                        // Перемещаем файл
                        if (move_uploaded_file($tmp_name, $filepath)) {
                            // Сохраняем информацию в БД
                            $stmt = $conn->prepare("INSERT INTO forum_attachments (topic_id, post_id, user_id, filename, original_filename, filepath, filesize, filetype) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                            $stmt->bind_param("iiiissis", $topic_id, $post_id, $user_id, $safe_filename, $original_name, $filepath, $file_size, $file_type);
                            $stmt->execute();
                            $stmt->close();
                        }
                    }
                }
            }
            
            // Обновляем время последнего обновления темы
            $conn->query("UPDATE forum_topics SET updated_at = NOW() WHERE id = $topic_id");
            
            $message = 'Ответ добавлен!';
            if ($upload_error) {
                $message .= ' Ошибки загрузки: ' . $upload_error;
            }
            
            redirect("forum_topic.php?id=$topic_id", $message);
        }
        $stmt->close();
    }
}

// Обработка удаления ответа
if (isset($_GET['delete_reply']) && isLoggedIn()) {
    $reply_id = (int)$_GET['delete_reply'];
    
    $stmt = $conn->prepare("SELECT user_id FROM forum_posts WHERE id = ?");
    $stmt->bind_param("i", $reply_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $reply = $result->fetch_assoc();
    $stmt->close();
    
    if ($reply && canEdit($reply['user_id'])) {
        // Удаляем файлы
        $stmt = $conn->prepare("SELECT filepath FROM forum_attachments WHERE post_id = ?");
        $stmt->bind_param("i", $reply_id);
        $stmt->execute();
        $files = $stmt->get_result();
        while ($file = $files->fetch_assoc()) {
            if (file_exists($file['filepath'])) {
                unlink($file['filepath']);
            }
        }
        $stmt->close();
        
        $conn->query("DELETE FROM forum_attachments WHERE post_id = $reply_id");
        $conn->query("DELETE FROM forum_posts WHERE id = $reply_id");
        redirect("forum_topic.php?id=$topic_id", 'Ответ удалён');
    }
}

$flash = getFlashMessage();
?>
<!DOCTYPE HTML>
<html>
<head>
    <title><?php echo escape($topic['title']); ?> — Форум ЖКХ</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <link rel="stylesheet" href="assets/css/main.css" />
    <style>
        .topic-header {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .topic-header.pinned { background: #fff3cd; }
        .topic-header.closed { background: #e0e0e0; }
        
        .topic-meta {
            color: #666;
            font-size: 14px;
            margin-top: 15px;
        }
        
        .badge {
            background: #2196F3;
            color: white;
            padding: 3px 10px;
            border-radius: 3px;
            font-size: 12px;
            margin-left: 5px;
        }
        .badge.pinned { background: #ff9800; }
        .badge.closed { background: #999; }
        .badge.admin { background: #ff5722; }
        
        .post-item {
            background: #fff;
            padding: 20px;
            margin: 15px 0;
            border-left: 3px solid #2196F3;
            border-radius: 4px;
        }
        
        .post-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .post-author {
            font-weight: bold;
            color: #333;
        }
        
        .post-actions {
            font-size: 13px;
        }
        .post-actions a {
            color: #666;
            margin-left: 10px;
        }
        .post-actions a:hover {
            color: #d32f2f;
        }
        
        .attachments {
            margin-top: 15px;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 4px;
        }
        
        .attachment-item {
            display: inline-block;
            margin: 5px 10px 5px 0;
            padding: 8px 12px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
        }
        .attachment-item:hover {
            background: #e3f2fd;
        }
        
        .attachment-preview {
            max-width: 200px;
            max-height: 200px;
            margin: 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .reply-form {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
        }
        
        .reply-form textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            min-height: 150px;
            font-family: inherit;
            font-size: 16px;
        }
        
        .file-upload {
            margin: 15px 0;
        }
        
        .file-upload input[type="file"] {
            padding: 10px;
            border: 2px dashed #ddd;
            border-radius: 4px;
            width: 100%;
        }
        
        .file-info {
            font-size: 13px;
            color: #666;
            margin-top: 5px;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
        }
        .btn-primary {
            background: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .btn-primary:hover {
            background: #45a049;
        }
        .btn-back {
            background: #999;
            color: white;
        }
        .btn-back:hover {
            background: #777;
        }
        
        .flash-message {
            background: #4CAF50;
            color: white;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .admin-controls {
            background: #fff3cd;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .admin-controls a {
            display: inline-block;
            margin-right: 10px;
            padding: 8px 15px;
            background: #ff9800;
            color: white;
            border-radius: 4px;
            text-decoration: none;
        }
        .admin-controls a:hover {
            background: #f57c00;
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
                <a href="onecolumn.php" class="btn btn-back">← Назад к списку тем</a>

                <?php if ($flash): ?>
                    <div class="flash-message"><?php echo escape($flash); ?></div>
                <?php endif; ?>

                <!-- Админ-контролы -->
                <?php if (isAdmin()): ?>
                    <div class="admin-controls">
                        <strong>🛠️ Управление темой:</strong>
                        <a href="forum_manage.php?action=pin&id=<?php echo $topic_id; ?>">
                            <?php echo $topic['is_pinned'] ? '📍 Открепить' : '📌 Закрепить'; ?>
                        </a>
                        <a href="forum_manage.php?action=close&id=<?php echo $topic_id; ?>">
                            <?php echo $topic['is_closed'] ? '🔓 Открыть' : '🔒 Закрыть'; ?>
                        </a>
                        <a href="forum_manage.php?action=delete&id=<?php echo $topic_id; ?>" 
                           onclick="return confirm('Удалить тему?')">
                            🗑️ Удалить тему
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Заголовок темы -->
                <div class="topic-header <?php echo $topic['is_pinned'] ? 'pinned' : ''; ?> <?php echo $topic['is_closed'] ? 'closed' : ''; ?>">
                    <h2>
                        <?php echo escape($topic['title']); ?>
                        <?php if ($topic['is_pinned']): ?>
                            <span class="badge pinned">📌 Закреплено</span>
                        <?php endif; ?>
                        <?php if ($topic['is_closed']): ?>
                            <span class="badge closed">🔒 Закрыто</span>
                        <?php endif; ?>
                    </h2>
                    <p><?php echo nl2br(escape($topic['content'])); ?></p>
                    
                    <!-- Вложения темы -->
                    <?php
                    $stmt = $conn->prepare("SELECT * FROM forum_attachments WHERE topic_id = ? AND post_id IS NULL");
                    $stmt->bind_param("i", $topic_id);
                    $stmt->execute();
                    $topic_files = $stmt->get_result();
                    
                    if ($topic_files->num_rows > 0): ?>
                        <div class="attachments">
                            <strong>📎 Вложения:</strong><br>
                            <?php while ($file = $topic_files->fetch_assoc()): ?>
                                <?php if (strpos($file['filetype'], 'image') !== false): ?>
                                    <a href="<?php echo $file['filepath']; ?>" target="_blank">
                                        <img src="<?php echo $file['filepath']; ?>" 
                                             alt="<?php echo escape($file['original_filename']); ?>" 
                                             class="attachment-preview">
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo $file['filepath']; ?>" target="_blank" class="attachment-item">
                                        📄 <?php echo escape($file['original_filename']); ?> 
                                        (<?php echo round($file['filesize'] / 1024, 1); ?> KB)
                                    </a>
                                <?php endif; ?>
                            <?php endwhile; ?>
                        </div>
                    <?php endif; 
                    $stmt->close();
                    ?>
                    
                    <div class="topic-meta">
                        <strong>Автор:</strong> <?php echo escape($topic['author_name']); ?>
                        <?php if ($topic['author_role'] === 'admin'): ?>
                            <span class="badge admin">АДМИН</span>
                        <?php endif; ?>
                        | <strong>Создано:</strong> <?php echo $topic['created_at']; ?>
                        | <strong>Просмотров:</strong> <?php echo $topic['views']; ?>
                    </div>
                </div>

                <!-- Ответы -->
                <h3>Ответы:</h3>
                <?php
                $stmt = $conn->prepare("
                    SELECT p.*, u.username, u.role
                    FROM forum_posts p
                    LEFT JOIN users u ON p.user_id = u.id
                    WHERE p.topic_id = ?
                    ORDER BY p.created_at ASC
                ");
                $stmt->bind_param("i", $topic_id);
                $stmt->execute();
                $replies = $stmt->get_result();

                if ($replies->num_rows > 0) {
                    while ($reply = $replies->fetch_assoc()) {
                        echo "<div class='post-item'>";
                        echo "<div class='post-header'>";
                        echo "<div>";
                        echo "<span class='post-author'>" . escape($reply['username']) . "</span>";
                        if ($reply['role'] === 'admin') {
                            echo " <span class='badge admin'>АДМИН</span>";
                        }
                        echo " <span style='color: #999; font-size: 13px;'>" . $reply['created_at'] . "</span>";
                        echo "</div>";
                        
                        if (isLoggedIn() && canEdit($reply['user_id'])) {
                            echo "<div class='post-actions'>";
                            echo "<a href='?id=$topic_id&delete_reply={$reply['id']}' onclick='return confirm(\"Удалить ответ?\")'>🗑️ Удалить</a>";
                            echo "</div>";
                        }
                        
                        echo "</div>";
                        echo "<p>" . nl2br(escape($reply['content'])) . "</p>";
                        
                        // Вложения ответа
                        $stmt2 = $conn->prepare("SELECT * FROM forum_attachments WHERE post_id = ?");
                        $stmt2->bind_param("i", $reply['id']);
                        $stmt2->execute();
                        $reply_files = $stmt2->get_result();
                        
                        if ($reply_files->num_rows > 0) {
                            echo "<div class='attachments'>";
                            echo "<strong>📎 Вложения:</strong><br>";
                            while ($file = $reply_files->fetch_assoc()) {
                                if (strpos($file['filetype'], 'image') !== false) {
                                    echo "<a href='{$file['filepath']}' target='_blank'>";
                                    echo "<img src='{$file['filepath']}' alt='" . escape($file['original_filename']) . "' class='attachment-preview'>";
                                    echo "</a>";
                                } else {
                                    echo "<a href='{$file['filepath']}' target='_blank' class='attachment-item'>";
                                    echo "📄 " . escape($file['original_filename']) . " (" . round($file['filesize'] / 1024, 1) . " KB)";
                                    echo "</a>";
                                }
                            }
                            echo "</div>";
                        }
                        $stmt2->close();
                        
                        echo "</div>";
                    }
                } else {
                    echo "<p>Пока нет ответов. Будьте первым!</p>";
                }
                $stmt->close();
                ?>

                <!-- Форма ответа -->
                <?php if (isLoggedIn()): ?>
                    <?php if (!$topic['is_closed']): ?>
                        <div class="reply-form">
                            <h3>Оставить ответ:</h3>
                            <form method="POST" enctype="multipart/form-data">
                                <textarea name="content" placeholder="Ваш ответ..." required></textarea>
                                
                                <div class="file-upload">
                                    <label><strong>📎 Прикрепить файлы (необязательно):</strong></label>
                                    <input type="file" name="attachments[]" multiple accept="image/*,.pdf,.doc,.docx">
                                    <div class="file-info">
                                        Можно загрузить: изображения (JPG, PNG, GIF), PDF, Word документы. Максимум 5 МБ на файл.
                                    </div>
                                </div>
                                
                                <button type="submit" name="add_reply" class="btn btn-primary">💬 Отправить ответ</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <p style="color: #999; padding: 20px; text-align: center;">
                            🔒 Тема закрыта. Ответы невозможны.
                        </p>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="reply-form">
                        <p><a href="login.php">Войдите</a>, чтобы оставить ответ.</p>
                    </div>
                <?php endif; ?>

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