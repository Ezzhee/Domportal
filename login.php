<?php
include 'db.php';
session_start();

// Если уже авторизован, перенаправляем
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = "Заполните все поля";
    } else {
        // Используем prepared statement для безопасности
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if ($user && password_verify($password, $user['password'])) {
            // Сохраняем данные пользователя в сессии (без пароля!)
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role']
            ];
            
            // Перенаправляем на главную
            redirect('index.php', 'Добро пожаловать, ' . $user['username'] . '!');
        } else {
            $error = "Неверный логин или пароль";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Вход — ЖКХ Портал</title>
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
            background: #2196F3;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        .auth-form button:hover {
            background: #1976D2;
        }
        .error {
            color: #d32f2f;
            background: #ffebee;
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
                                <a href="register.php">Регистрация</a>
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
                    <h2>Вход в систему</h2>
                    
                    <?php if ($error): ?>
                        <div class="error">❌ <?php echo escape($error); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <input type="text" name="username" placeholder="Логин" 
                               value="<?php echo isset($_POST['username']) ? escape($_POST['username']) : ''; ?>" required>
                        <input type="password" name="password" placeholder="Пароль" required>
                        <button type="submit">Войти</button>
                    </form>
                    
                    <div class="auth-links">
                        Нет аккаунта? <a href="register.php">Зарегистрироваться</a>
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