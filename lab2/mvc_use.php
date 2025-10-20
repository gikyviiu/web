<?php

require_once 'patterns/mvc/controllers/router.php';
require_once 'patterns/mvc/models/user.php';
require_once 'patterns/mvc/models/users.php';
require_once 'patterns/mvc/views/markdownview.php';


use Mvc\Views\MarkdownView;
use Mvc\Models\Users;

$usersObj = new Users();
$users = $usersObj->collection;

$view = new MarkdownView($users);
echo nl2br($view->render());