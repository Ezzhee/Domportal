<?php
// Настройки подключения
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "domportal";

// Подключение к БД
$conn = new mysqli($servername, $username, $password, $dbname);

// Проверка подключения
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Устанавливаем кодировку
$conn->set_charset("utf8mb4");

// ============================================
// ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ
// ============================================

// Проверка авторизации
function isLoggedIn() {
    return isset($_SESSION['user']);
}

// Проверка роли админа/редактора
function isAdmin() {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
}

// Получить текущего пользователя
function getCurrentUser() {
    return $_SESSION['user'] ?? null;
}

// Безопасный вывод HTML
function escape($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Проверка прав на редактирование (автор или админ)
function canEdit($authorId) {
    if (!isLoggedIn()) return false;
    $user = getCurrentUser();
    return $user['id'] == $authorId || isAdmin();
}

// Получить информацию о пользователе по ID
function getUserById($conn, $userId) {
    $stmt = $conn->prepare("SELECT id, username, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Перенаправление с сообщением
function redirect($url, $message = '') {
    if ($message) {
        $_SESSION['flash_message'] = $message;
    }
    header("Location: $url");
    exit;
}

// Показать и очистить flash-сообщение
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}
?>