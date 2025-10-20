<?php

require_once 'patterns/factory/router.php';
require_once 'patterns/factory/models/collection.php';
require_once 'patterns/factory/models/user.php';   
require_once 'patterns/factory/models/users.php';

use Factory\Models\Users;

$usersObj = new Users();

foreach ($usersObj->users as $user) {
    echo "Email: " . $user->email . "<br>";
    echo "Name: " . $user->first_name . " " . $user->last_name . "<br>";
    echo "<br>";
}