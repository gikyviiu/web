<?php
require_once 'NewsDB.class.php';
$news = new NewsDB();
$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    die("Новость не найдена.");
}

$all = $news->getNews();
$item = null;
foreach ($all as $n) {
    if ($n['id'] == $id) {
        $item = $n;
        break;
    }
}

if (!$item) {
    die("Новость не найдена.");
}
?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title><?= htmlspecialchars($item['title']) ?></title></head>
<body>
    <h1><?= htmlspecialchars($item['title']) ?></h1>
    <p><strong>Категория:</strong> <?= htmlspecialchars($item['category']) ?></p>
    <p><strong>Источник:</strong> <?= htmlspecialchars($item['source']) ?></p>
    <p><em><?= date('d.m.Y H:i', $item['datetime']) ?></em></p>
    <hr>
    <p><?= nl2br(htmlspecialchars($item['description'])) ?></p>
    <br>
    <a href="news.php">&larr; Назад к списку новостей</a>
</body>
</html>