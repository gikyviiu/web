<?php
session_start();

$message = '';

// Обработка отправки формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['captcha'])) {
        $user_input = trim($_POST['captcha']);
        $correct_captcha = $_SESSION['captcha_code'] ?? '';

        if ($user_input === $correct_captcha) {
            $message = '<p class="success">✅ Отлично! Вы правильно ввели символы с изображения.</p>';
        } else {
            $message = "<strong>❌ Неправильно!</strong> Вы ввели: " . htmlspecialchars($user_input) . ". Правильный ответ: " . htmlspecialchars($correct_captcha);
        }

        unset($_SESSION['captcha_code']);
    } else {
        $message = '<p class="error">❌ Ошибка! Пожалуйста, введите символы с изображения.</p>';
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация</title>
    <style>
        .error { color: red; }
        .success { color: green; }
        #image-disabled-warning {
            display: none;
            color: red;
            font-weight: bold;
            margin: 15px 0;
            padding: 10px;
            border: 1px solid red;
            background-color: #fff0f0;
        }
    </style>
</head>
<body>

<h2>Проверка CAPTCHA</h2>

<!-- Форма с CAPTCHA -->
<form id="captcha-form" method="POST" action="">
    <div id="captcha-container">
        <img id="captcha-img" src="noise-picture.php" alt="CAPTCHA" style="border: 1px solid #ccc;">
        <br><br>
        <label for="captcha">Введите символы с картинки:</label><br>
        <input type="text" name="captcha" id="captcha" required>
        <br><br>
        <input type="submit" value="Отправить" id="submit-btn">
    </div>
</form>

<!-- Сообщение об отключённых изображениях -->
<div id="image-disabled-warning">
    ⚠️ Внимание: изображения в вашем браузере, вероятно, отключены или заблокированы. Без CAPTCHA проверка невозможна.
</div>

    

<?php
if (!empty($message)) {
    echo "<hr><div style='margin-top: 20px; padding: 10px; border: 1px solid #ddd; background: #f9f9f9;'>$message</div>";
}
?>

</body>
</html>