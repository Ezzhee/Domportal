<?php
include 'db.php';
session_start();

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    
    // Валидация
    if (empty($username) || empty($password)) {
        $error = "Заполните все поля";
    } elseif (strlen($username) < 3) {
        $error = "Логин должен быть не менее 3 символов";
    } elseif (strlen($password) < 6) {
        $error = "Пароль должен быть не менее 6 символов";
    } elseif ($password !== $password_confirm) {
        $error = "Пароли не совпадают";
    } else {
        // Проверяем, существует ли пользователь
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Пользователь с таким логином уже существует";
        } else {
            // Регистрируем пользователя
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'user')");
            $stmt->bind_param("ss", $username, $password_hash);
            
            if ($stmt->execute()) {
                $success = "Регистрация успешна! Можете войти.";
            } else {
                $error = "Ошибка регистрации: " . $conn->error;
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Регистрация — ЖКХ Портал</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <link rel="stylesheet" href="assets/css/main.css" />
    <style>
        .auth-form {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            background: #f9f9f9;
            border-radius: 8px;
        }
        .auth-form input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .auth-form button {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        .auth-form button:hover {
            background: #45a049;
        }
        .error {
            color: #d32f2f;
            background: #ffebee;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .success {
            color: #388e3c;
            background: #e8f5e9;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .auth-links {
            text-align: center;
            margin-top: 20px;
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
                                <a href="login.php">Вход</a>
                            </nav>
                        </header>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main -->
        <div id="main">
            <div class="container">
                <div class="auth-form">
                    <h2>Регистрация</h2>
                    
                    <?php if ($error): ?>
                        <div class="error">❌ <?php echo escape($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="success">✅ <?php echo escape($success); ?></div>
                        <div class="auth-links">
                            <a href="login.php" class="button">Войти в систему</a>
                        </div>
                    <?php else: ?>
                        <form method="POST">
                            <input type="text" name="username" placeholder="Логин (минимум 3 символа)" 
                                   value="<?php echo isset($_POST['username']) ? escape($_POST['username']) : ''; ?>" required>
                            <input type="password" name="password" placeholder="Пароль (минимум 6 символов)" required>
                            <input type="password" name="password_confirm" placeholder="Повторите пароль" required>
                            <button type="submit">Зарегистрироваться</button>
                        </form>
                        
                        <div class="auth-links">
                            Уже есть аккаунт? <a href="login.php">Войти</a>
                        </div>
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