<?php
error_reporting(E_ALL|E_STRICT);
ini_set('display_errors', 'On');

$loader = require '../../../vendor/autoload.php';



$client = new Drest\Client('http://drest-example1.localhost');

$client->get('/user/1');
