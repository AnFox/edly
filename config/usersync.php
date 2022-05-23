<?php

return [
    'route_attributes' => [
        'prefix' => 'api/usersync',
        'namespace' => 'Illion\UserSync\Http\Controllers',
        'middleware' => ['checkClientScopes:sync-users'],
        'user_sync_endpoint' => '/api/usersync/sync',
    ],
    'pubKeyPath' => 'oauth-public.key',
];
