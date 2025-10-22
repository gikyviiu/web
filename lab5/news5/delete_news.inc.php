<?php

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    return;
}

$id = (int)$id;

if ($news->deleteNews($id)) {
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
} else {
    $errMsg = "Не удалось удалить новость.";
}
