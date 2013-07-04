<?php
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', '1');

$loader = require __DIR__ . '/../../vendor/autoload.php';

if (! isset($loader)) {
    throw new Exception('Unable to load autoload.php. Try running `php composer.phar install`');
}