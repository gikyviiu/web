<?php
require_once 'patterns/singleton/settings.php';

use Singleton\Settings;

Settings::get()->number = 42;
Settings::get()->text = "Hello";
Settings::get()->flag = true;

echo "<pre>";
echo Settings::get()->number . "\n";
echo Settings::get()->text . "\n"; 
echo (Settings::get()->flag ? 'true' : 'false') . "\n"; 
echo "</pre>";