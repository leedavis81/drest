<?php
error_reporting(E_ALL|E_STRICT);
ini_set('display_errors', 'On');

$loader = require '../../../vendor/autoload.php';

require '../Client/Entities/User.php';
require '../Client/Entities/Profile.php';
require '../Client/Entities/PhoneNumber.php';


$client = new Drest\Client('http://drest-example1.localhost', 'Json');


/* Post an item
$user = Client\Entities\User::create()
        ->setEmailAddress('hello@somewhere.com')
        ->setUsername('leedavis81')
        ->setProfile(Client\Entities\Profile::create()
            ->setTitle('mr')
            ->setFirstname('lee')
            ->setLastname('davis'))
        ->addPhoneNumbers(array(
            Client\Entities\PhoneNumber::create()->setNumber('02087856589'),
            Client\Entities\PhoneNumber::create()->setNumber('07584565445')))
        ->addPhoneNumber(Client\Entities\PhoneNumber::create()->setNumber('02078545896'));

try
{
    $response = $client->post('/user', $user);
    if (($location = $response->getHttpHeader('Location')) !== null)
    {
        echo 'The resource was created at: ' . $location;
    }
} catch (\Drest\Error\ErrorException $e)
{
    echo $e->getErrorDocument()->render();
}
*/

/* get an item

try {
    $response = $client->get('user/85');

    echo $response->getRepresentation();
} catch (\Drest\Error\ErrorException $e)
{
    echo $e->getErrorDocument()->render();
}
*/


/* put (update) an item

$user = Client\Entities\User::create()
        ->setEmailAddress('newemail@somewhere2.com');
try
{
    $response = $client->put('/user/105', $user);
    if ($response->getStatusCode() == 200)
    {
        echo 'Entity was successfully updated';
    }
} catch (\Drest\Error\ErrorException $e)
{
    echo $e->getErrorDocument()->render();
}
*/



/* patch (update - partial) an item

$user = Client\Entities\User::create()
        ->setEmailAddress('newemail@somewhere2.com');
try
{
    $response = $client->patch('/user/106', $user);
    if ($response->getStatusCode() == 200)
    {
        echo 'Entity was successfully updated';
    }
} catch (\Drest\Error\ErrorException $e)
{
    echo $e->getErrorDocument()->render();
}

*/

