<?php
include 'db.php';
session_start();

// Только для админов
if (!isAdmin()) {
    redirect('onecolumn.php', 'Доступ запрещён');
}

$action = $_GET['action'] ?? '';
$topic_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($topic_id <= 0) {
    redirect('onecolumn.php', 'Тема не найдена');
}

switch ($action) {
    case 'pin':
        // Переключаем закрепление
        $conn->query("UPDATE forum_topics SET is_pinned = NOT is_pinned WHERE id = $topic_id");
        redirect("forum_topic.php?id=$topic_id", 'Статус закрепления изменён');
        break;
        
    case 'close':
        // Переключаем закрытие
        $conn->query("UPDATE forum_topics SET is_closed = NOT is_closed WHERE id = $topic_id");
        redirect("forum_topic.php?id=$topic_id", 'Тема ' . ($topic_id ? 'закрыта' : 'открыта'));
        break;
        
    case 'delete':
        // Удаляем тему и все ответы
        $conn->query("DELETE FROM forum_posts WHERE topic_id = $topic_id");
        $conn->query("DELETE FROM forum_topics WHERE id = $topic_id");
        redirect('onecolumn.php', 'Тема удалена');
        break;
        
    default:
        redirect('onecolumn.php', 'Неизвестное действие');
}
?>