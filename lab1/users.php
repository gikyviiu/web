<?php

declare(strict_types=1);

spl_autoload_register(function ($className) {
    if (strpos($className, 'Classes\\') === 0) {
        $fileName = str_replace('\\', '/', $className) . '.php';
        $filePath = __DIR__ . '/' . $fileName;
        if (file_exists($filePath)) {
            require_once $filePath;
        }
    }
});

use Classes\User;
use Classes\SuperUser;

echo "<pre>";

$user1 = new User("Мария Соколова", "maria_s", "mnrstrns");
$user2 = new User("Дмитрий Козлов", "dmitry_k", "dnt5jwha9");
$user3 = new User("Екатерина Волкова", "ekaterina_v", "hq6jagh");

$super = new SuperUser("Админ", "admin", "jw6jsann", "Администратор");

echo "Информация о пользователях:\n\n";
$user1->showInfo();
$user2->showInfo();
$user3->showInfo();

echo "\nИнформация о суперпользователе:\n\n";
$super->showInfo();

echo "\nДанные суперпользователя через getInfo():\n";
print_r($super->getInfo());

echo "\nВсего обычных пользователей: " . User::$count . "\n";
echo "Всего супер-пользователей: " . SuperUser::$count . "\n";

echo "</pre>";
