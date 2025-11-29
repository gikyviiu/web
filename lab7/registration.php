<?php
session_start();

$message = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['captcha'])) {
        $user_input = trim($_POST['captcha']);
        $correct_captcha = $_SESSION['captcha_code'] ?? '';

        if ($user_input === $correct_captcha) {
            $message = '<p class="success">✅ Успех!</p>';
        } else {
            $message = '<p class="error"> ❌ Неверные символы!</p>';
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


<div id="image-disabled-warning">
    ⚠️ Внимание: изображения в вашем браузере отключены или заблокированы. Без CAPTCHA проверка невозможна.
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const img = document.getElementById('captcha-img');
    const warning = document.getElementById('image-disabled-warning');
    const container = document.getElementById('captcha-container');
    const form = document.getElementById('captcha-form');
    const input = document.getElementById('captcha');
    const submitBtn = document.getElementById('submit-btn');

    let loaded = false;

    img.onload = function () {
        loaded = true;
    };

    img.onerror = function () {
        loaded = true;
        showImageDisabledWarning();
    };

    // Проверка через 2 секунды
    setTimeout(function () {
        if (!loaded) {
            showImageDisabledWarning();
        }
    }, 2000);

    function showImageDisabledWarning() {
        // Скрываем CAPTCHA
        container.style.display = 'none';

        // Показываем предупреждение
        warning.style.display = 'block';

        // Удаляем поле и кнопку, чтобы нельзя было отправить
        if (input) input.remove();
        if (submitBtn) submitBtn.remove();

        // Блокируем отправку формы
        form.onsubmit = function(e) {
            e.preventDefault();
            alert("Изображения отключены. Проверка CAPTCHA невозможна.");
        };
    }
});
</script>

<?php
if (!empty($message)) {
    echo "<hr><div style='margin-top: 20px; padding: 10px; border: 1px solid #ddd; background: #f9f9f9;'>$message</div>";
}
?>

</body>
</html>