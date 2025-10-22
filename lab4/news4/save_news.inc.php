<?php
// 10. Проверьте, была ли корректным образом отправлена HTML-форма

// Проверяем, что все поля заполнены
if (
    empty($_POST['title']) ||
    empty($_POST['category']) ||
    empty($_POST['description']) ||
    empty($_POST['source'])
) {
    // Если НЕТ — присвойте переменной $errMsg строковое значение "Заполните все поля формы!"
    $errMsg = "Заполните все поля формы!";
    return; // Прерываем выполнение, чтобы не продолжать
}

// Если ДА — отфильтруйте полученные данные и вызовите метод saveNews()
$title = trim($_POST['title']);
$category = (int)$_POST['category']; // Приводим к целому числу
$description = trim($_POST['description']);
$source = trim($_POST['source']);

// Получаем имя категории для передачи в saveNews() (как в NewsDB.class.php)
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
        $categoryName = 'Неизвестная категория'; // или можно бросить ошибку
}

// 11. С помощью возвращаемого методом значения проверьте, был ли запрос успешным
if ($news->saveNews($title, $categoryName, $description, $source)) {
    // Если ДА — выполните перезапрос страницы news.php
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
} else {
    // Если НЕТ — присвойте переменной $errMsg строковое значение "Произошла ошибка при добавлении новости"
    $errMsg = "Произошла ошибка при добавлении новости";
}