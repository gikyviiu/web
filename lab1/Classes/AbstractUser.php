<?php

declare(strict_types=1);

namespace Classes;

/**
 * [Description AbstractUser]
 */
abstract class AbstractUser {
    public string $name;
    public string $login;
    protected string $password;

    /**
     * @param string $name
     * @param string $login
     * @param string $password
     */
    public function __construct(string $name, string $login, string $password) {
        $this->name = $name;
        $this->login = $login;
        $this->password = $password;
    }

    /**
     * @return void
     */
    abstract public function showInfo(): void;

    /**
     * @return [type]
     */
    public function __destruct() {
        echo "Пользователь [{$this->login}] удален.<br>";
    }
}