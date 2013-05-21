<?php
error_reporting(E_ALL|E_STRICT);
ini_set('display_errors', 'On');

$loader = require '../../../vendor/autoload.php';

require '../Reps/User.php';

$client = new Drest\Client('http://drest-example1.localhost', new Drest\Representation\Json());


//echo 'Getting a user' . PHP_EOL;
//$user = $client->get('/user/1', 'title|firstname');


$user = new \Reps\User(new Drest\Representation\Json());
$user->email_address = 'hello@somewhere.com';
$user->username = 'hatchet';

$client->post('/users', $user);

var_dump($user);