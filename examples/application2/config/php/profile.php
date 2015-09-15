<?php

$resources = [];

$resources['Entities\Profile'] = [
    'representations' => ['Json'],
    'routes' => [
        ['name' => 'get_profile', 'routePattern' => '/profile/:id', 'verbs' => ['GET'], 'content' => 'element'],
        ['name' => 'post_profile', 'routePattern' => '/profile', 'verbs' => ['POST'], 'content' => 'element', 'handle_call' => 'populateProfile'],
        ['name' => 'get_profiles', 'routePattern' => '/profiles', 'verbs' => ['GET'], 'content' => 'collection']
    ]
];


return $resources;