<?php
namespace Factory\Models;


class Users extends Collection
{
    public function __construct(public ?array $users = null)
    {
        $users ??= [
            new User('natalia.volkova@example.com', 'password', 'Наталья', 'Волкова'),
            new User('sergey.sokolov@example.com', 'password', 'Сергей', 'Соколов'),
            new User('marina.lebedeva@example.com', 'password', 'Марина', 'Лебедева'),
            new User('andrey.novikov@example.com', 'password', 'Андрей', 'Новиков'),
            new User('tatiana.morozova@example.com', 'password', 'Татьяна', 'Морозова'),
        ];
        $this->users = $users;
        parent::__construct($users);
    }
}