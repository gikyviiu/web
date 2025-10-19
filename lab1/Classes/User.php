<?php

declare(strict_types=1);

namespace Classes;

/**
 * [Description User]
 */
class User extends AbstractUser {
    public static int $count = 0; 

    /**
     * @return void
     */
    public function showInfo(): void {
        echo "Пользователь: {$this->name}\n";
        echo "Логин: {$this->login}\n";
        echo "Пароль: {$this->password}\n";
        echo "-------------------------\n";
    }

    /**
     * @param string $name
     * @param string $login
     * @param string $password
     */
    public function __construct(string $name, string $login, string $password) {
        parent::__construct($name, $login, $password);
        self::$count++; 
    }
}