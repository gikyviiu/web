<?php

declare(strict_types=1);

namespace Classes;

/**
 * [Description SuperUser]
 */
class SuperUser extends User implements SuperUserInterface {
    public static int $count = 0;

    public string $role;

    /**
     * @param string $name
     * @param string $login
     * @param string $password
     * @param string $role
     */
    public function __construct(string $name, string $login, string $password, string $role) {
        parent::__construct($name, $login, $password);
        $this->role = $role;
        self::$count++;
    }

    /**
     * @return void
     */
    public function showInfo(): void {
        parent::showInfo();
        echo "Роль: {$this->role}\n";
    }

    /**
     * @return array
     */
    public function getInfo(): array {
        return [
            'name' => $this->name,
            'login' => $this->login,
            'role' => $this->role
        ];
    }
}