<?php

$resources = [];

$resources['Entities\User'] = [
    'representations' => ['Json'],
    'routes' => [
        ['name' => 'get_user', 'routePattern' => '/user/:id', 'routeConditions' => ['id' => '\d+'], 'verbs' => ['GET'], 'action' => 'Action\Custom', 'origin' => 'get_user'],
        ['name' => 'get_user_profile', 'routePattern' => '/user/:id/profile', 'verbs' => ['GET'], 'expose' => ['profile']],
        ['name' => 'get_user_numbers', 'routePattern' => '/user/:id/numbers', 'verbs' => ['GET'], 'expose' => ['phone_numbers']],
        ['name' => 'post_user', 'routePattern' => '/user', 'verbs' => ['POST'], 'expose' => ['username', 'email_address', 'profile' => ['firstname', 'lastname'], 'phone_numbers' => ['number']], 'handle_call' => 'populatePost'],
        ['name' => 'get_users', 'routePattern' => '/users', 'verbs' => ['GET'], 'collection' => 'true', 'expose' => ['username', 'email_address', 'profile', 'phone_numbers']],
        ['name' => 'update_user', 'routePattern' => '/user/:id', 'routeConditions' => ['id' => '\d+'], 'verbs' => ['PUT', 'PATCH'], 'expose' => ['email_address', 'profile' => ['firstname', 'lastname']], 'handle_call' => 'patchUser'],
        ['name' => 'delete_user', 'routePattern' => '/user/:id', 'verbs' => ['DELETE']],
        ['name' => 'delete_users', 'routePattern' => '/users', 'collection' => 'true', 'verbs' => ['DELETE']]
    ]
];

return $resources;