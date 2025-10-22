<?php

if (
    empty($_POST['title']) ||
    empty($_POST['category']) ||
    empty($_POST['description']) ||
    empty($_POST['source'])
) {
    $errMsg = "Заполните все поля формы!";
    return;
}

$title = trim($_POST['title']);
$category = (int)$_POST['category']; 
$description = trim($_POST['description']);
$source = trim($_POST['source']);

$categoryName = '';
switch ($category) {
    case 1:
        $categoryName = 'Политика';
        break;
    case 2:
        $categoryName = 'Культура';
        break;
    case 3:
        $categoryName = 'Спорт';
        break;
    default:
        $categoryName = 'Неизвестная категория';
}

if ($news->saveNews($title, $categoryName, $description, $source)) {
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
} else {
    $errMsg = "Произошла ошибка при добавлении новости";
}