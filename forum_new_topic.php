<?php
include 'db.php';
session_start();

// Проверяем авторизацию
if (!isLoggedIn()) {
    redirect('login.php', 'Войдите, чтобы создать тему');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $user_id = getCurrentUser()['id'];
    
    if (empty($title) || empty($content)) {
        $error = 'Заполните все поля';
    } elseif (mb_strlen($title) < 5) {
        $error = 'Заголовок должен содержать не менее 5 символов';
    } elseif (mb_strlen($content) < 10) {
        $error = 'Содержимое должно содержать не менее 10 символов';
    } else {
        // Создаём тему
        $stmt = $conn->prepare("INSERT INTO forum_topics (title, content, author_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $title, $content, $user_id);
        
        if ($stmt->execute()) {
            $topic_id = $conn->insert_id;
            
            // Обрабатываем загрузку файлов
            if (isset($_FILES['attachments']) && !empty($_FILES['attachments']['name'][0])) {
                $upload_dir = 'uploads/forum/';
                
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
                        
                        if (in_array($file_type, $allowed_types) && $file_size <= $max_size) {
                            $file_ext = pathinfo($original_name, PATHINFO_EXTENSION);
                            $safe_filename = uniqid() . '_' . time() . '.' . $file_ext;
                            $filepath = $upload_dir . $safe_filename;
                            
                            if (move_uploaded_file($tmp_name, $filepath)) {
                                $stmt2 = $conn->prepare("INSERT INTO forum_attachments (topic_id, user_id, filename, original_filename, filepath, filesize, filetype) VALUES (?, ?, ?, ?, ?, ?, ?)");
                                $stmt2->bind_param("iisssis", $topic_id, $user_id, $safe_filename, $original_name, $filepath, $file_size, $file_type);
                                $stmt2->execute();
                                $stmt2->close();
                            }
                        }
                    }
                }
            }
            
            redirect("forum_topic.php?id=$topic_id", 'Тема успешно создана!');
        } else {
            $error = 'Ошибка при создании темы: ' . $conn->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Создать тему — Форум ЖКХ</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <link rel="stylesheet" href="assets/css/main.css" />
    <style>
        .form-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 30px;
            background: #f9f9f9;
            border-radius: 8px;
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
            min-height: 200px;
            resize: vertical;
        }
        .btn-submit {
            background: #4CAF50;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        .btn-submit:hover {
            background: #45a049;
        }
        .btn-cancel {
            background: #999;
            color: white;
            padding: 12px 30px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            margin-left: 10px;
        }
        .btn-cancel:hover {
            background: #777;
        }
        .error {
            background: #ffebee;
            color: #d32f2f;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
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
                                <a href="logout.php">Выход (<?php echo escape(getCurrentUser()['username']); ?>)</a>
                            </nav>
                        </header>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main -->
        <div id="main">
            <div class="container">
                <div class="form-container">
                    <h2>Создать новую тему</h2>

                    <?php if ($error): ?>
                        <div class="error">❌ <?php echo escape($error); ?></div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>Заголовок темы:</label>
                            <input type="text" name="title" 
                                   placeholder="Введите заголовок (минимум 5 символов)" 
                                   value="<?php echo isset($_POST['title']) ? escape($_POST['title']) : ''; ?>" 
                                   required>
                        </div>

                        <div class="form-group">
                            <label>Содержимое:</label>
                            <textarea name="content" 
                                      placeholder="Опишите вашу проблему или вопрос (минимум 10 символов)" 
                                      required><?php echo isset($_POST['content']) ? escape($_POST['content']) : ''; ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>📎 Прикрепить файлы (необязательно):</label>
                            <input type="file" name="attachments[]" multiple accept="image/*,.pdf,.doc,.docx">
                            <small style="color: #666;">Можно загрузить: изображения, PDF, Word документы. Максимум 5 МБ на файл.</small>
                        </div>

                        <button type="submit" class="btn-submit">✅ Создать тему</button>
                        <a href="onecolumn.php" class="btn-cancel">Отмена</a>
                    </form>
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