<?php
include '../db.php';
session_start();

if (!isAdmin()) {
    redirect('../index.php', 'Доступ запрещён');
}

$flash = '';
$edit_mode = false;
$edit_item = null;

// Обработка добавления/редактирования
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_item'])) {
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);
        
        if (!empty($title) && !empty($content)) {
            $stmt = $conn->prepare("INSERT INTO helpful_info (title, content) VALUES (?, ?)");
            $stmt->bind_param("ss", $title, $content);
            if ($stmt->execute()) {
                $flash = '✅ Материал добавлен!';
            }
            $stmt->close();
        }
    }
    
    if (isset($_POST['update_item'])) {
        $id = (int)$_POST['id'];
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);
        
        if (!empty($title) && !empty($content)) {
            $stmt = $conn->prepare("UPDATE helpful_info SET title = ?, content = ? WHERE id = ?");
            $stmt->bind_param("ssi", $title, $content, $id);
            if ($stmt->execute()) {
                $flash = '✅ Материал обновлён!';
            }
            $stmt->close();
        }
    }
}

// Обработка удаления
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM helpful_info WHERE id = $id");
    $flash = '✅ Материал удалён!';
}

// Режим редактирования
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM helpful_info WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_item = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Получаем все материалы
$items = $conn->query("SELECT * FROM helpful_info ORDER BY created_at DESC");
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Управление полезной информацией — Админ-панель</title>
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
        .item-list {
            margin-top: 30px;
        }
        .item {
            background: white;
            padding: 20px;
            margin: 15px 0;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .item-actions {
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

                <h2>💡 Управление полезной информацией</h2>

                <!-- Форма добавления/редактирования -->
                <div class="admin-form">
                    <h3><?php echo $edit_mode ? 'Редактировать материал' : 'Добавить материал'; ?></h3>
                    <form method="POST">
                        <?php if ($edit_mode): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_item['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label>Заголовок:</label>
                            <input type="text" name="title" 
                                   value="<?php echo $edit_mode ? escape($edit_item['title']) : ''; ?>" 
                                   placeholder="Введите заголовок (например: Как оплатить ЖКХ онлайн)" required>
                        </div>

                        <div class="form-group">
                            <label>Содержание:</label>
                            <textarea name="content" 
                                      placeholder="Введите полезную информацию, советы или инструкции" 
                                      required><?php echo $edit_mode ? escape($edit_item['content']) : ''; ?></textarea>
                        </div>

                        <?php if ($edit_mode): ?>
                            <button type="submit" name="update_item" class="btn btn-primary">💾 Сохранить изменения</button>
                            <a href="helpful.php" class="btn btn-cancel">Отмена</a>
                        <?php else: ?>
                            <button type="submit" name="add_item" class="btn btn-primary">➕ Добавить материал</button>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Список материалов -->
                <div class="item-list">
                    <h3>Все материалы (<?php echo $items->num_rows; ?>)</h3>
                    <?php if ($items->num_rows > 0): ?>
                        <?php while ($item = $items->fetch_assoc()): ?>
                            <div class="item">
                                <h4><?php echo escape($item['title']); ?></h4>
                                <p><?php echo nl2br(escape(mb_substr($item['content'], 0, 200))); ?><?php echo mb_strlen($item['content']) > 200 ? '...' : ''; ?></p>
                                <small>Добавлено: <?php echo $item['created_at']; ?></small>
                                <div class="item-actions">
                                    <a href="?edit=<?php echo $item['id']; ?>" class="btn btn-small btn-edit">✏️ Редактировать</a>
                                    <a href="?delete=<?php echo $item['id']; ?>" 
                                       class="btn btn-small btn-delete" 
                                       onclick="return confirm('Удалить этот материал?')">🗑️ Удалить</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>Материалов пока нет.</p>
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