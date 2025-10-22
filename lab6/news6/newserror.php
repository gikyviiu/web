<?php
require_once 'NewsDBerror.class.php';

$news = new NewsDB();

$errMsg = "";

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    require_once 'delete_news.inc.php';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'save_news.inc.php';
}

?>

<!DOCTYPE html>
<html>
<head>
	<title>Новостная лента</title>
	<meta charset="utf-8">
	<style>
		body { font-family: Arial, sans-serif; margin: 20px; }
		.error { color: red; font-weight: bold; margin-bottom: 15px; }
		.news-item { margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
		.news-title a {
			font-size: 1.2em;
			font-weight: bold;
			color: #000;
			text-decoration: none;
		}
		.news-title a:hover {
			color: #0000EE;
			text-decoration: underline;
		}
		.news-meta { font-size: 0.9em; color: #555; }
		.news-source { font-style: italic; }
		.delete-link { color: red; text-decoration: none; margin-left: 10px; }
		.delete-link:hover { text-decoration: underline; }
	</style>
</head>
<body>
  <h1>Последние новости</h1>

  <?php if (!empty($errMsg)): ?>
    <div class="error"><?= htmlspecialchars($errMsg) ?></div>
  <?php endif; ?>

  <?php
  require_once 'get_news.inc.php';

  if (!empty($allNews)) {
      echo "<div class='news-list'>";
      foreach ($allNews as $item) {
          echo "<div class='news-item'>";
          echo "<div class='news-title'>";
          echo "<a href='view.php?id=" . $item['id'] . "'>" . htmlspecialchars($item['title']) . "</a>";
          echo "</div>";

          echo "<div class='news-meta'>";
          echo htmlspecialchars($item['category']) . " | ";
          echo "<span class='news-source'>" . htmlspecialchars($item['source']) . "</span> | ";
          echo date('d.m.Y H:i', $item['datetime']);
          echo " <a href='?id=" . $item['id'] . "' class='delete-link' onclick='return confirm(\"Удалить эту новость?\")'>[Удалить]</a>";
          echo "</div>";
          echo "<div class='news-description'>" . htmlspecialchars($item['description']) . "</div>";
          echo "</div>";
      }
      echo "</div>";
  } else {
      echo "<p>Новостей пока нет.</p>";
  }
  ?>

  <hr style="margin: 30px 0;">

  <form action="<?= $_SERVER['PHP_SELF']; ?>" method="post">
    <h2>Добавить новость</h2>
    Заголовок новости:<br>
    <input type="text" name="title" required style="width: 400px;"><br><br>

    Выберите категорию:<br>
    <select name="category" required>
      <option value="">-- выберите --</option>
      <?php foreach ($news as $id => $name): ?>
        <option value="<?= htmlspecialchars($id) ?>"><?= htmlspecialchars($name) ?></option>
      <?php endforeach; ?>
    </select><br><br>

    Текст новости:<br>
    <textarea name="description" cols="50" rows="5" required style="width: 400px;"></textarea><br><br>

    Источник:<br>
    <input type="text" name="source" required style="width: 400px;"><br><br>

    <input type="submit" value="Добавить!" style="padding: 8px 16px; font-size: 1em;">
  </form>
</body>
</html>