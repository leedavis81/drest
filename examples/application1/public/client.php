<?php
error_reporting(E_ALL|E_STRICT);
ini_set('display_errors', 'On');

$loader = require '../../../vendor/autoload.php';

require '../Client/Entities/User.php';
require '../Client/Entities/Profile.php';
require '../Client/Entities/PhoneNumber.php';


$client = new Drest\Client('http://drest-example1.localhost', new Drest\Representation\Xml());


//echo 'Getting a user' . PHP_EOL;
//$user = $client->get('/user/1', 'title|firstname');


$user = new \Client\Entities\User();

$user->email_address = 'hello@somewhere.com';
$user->username = 'hatchet';
$user->profile = new \Client\Entities\Profile();
$user->profile->title = 'Mr';
$user->profile->firstname = 'Soft';

$number1 = new \Client\Entities\PhoneNumber();
$number1->number = '02087854545';

$number2 = new \Client\Entities\PhoneNumber();
$number2->number = '02087854546';

$user->phone_numbers = array($number1, $number2);

try
{
    $client->post('/user', $user);
} catch (\Drest\Response\ErrorException $e)
{
    var_dump($e->getErrorDocument());
}


//var_dump($user);