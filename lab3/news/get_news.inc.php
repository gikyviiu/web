<?php

$allNews = $news->getNews();

if ($allNews === false) {
    $errMsg = "Произошла ошибка при выводе новостной ленты";
} else {
    $count = count($allNews);
    echo "<p><strong>Всего новостей: {$count}</strong></p>";
}
