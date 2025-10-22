<?php
// Получаем ID из URL
$id = $_GET['id'] ?? null;

// Проверяем, что ID передан и это число
if (!$id || !is_numeric($id)) {
    // Ничего не делаем — просто не обрабатываем
    return;
}

$id = (int)$id;

// Пытаемся удалить
if ($news->deleteNews($id)) {
    // Успешно — перенаправляем, чтобы убрать ?id=... из URL
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
} else {
    // Ошибка — показываем сообщение
    $errMsg = "Не удалось удалить новость.";
}
?>