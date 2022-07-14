<?php
// TODO for redirect, should I be using route() instead of building it myself?
return [
    'oauth2' => [
        'client_id' => getenv('LARDER_CLIENT_ID'),
        'client_secret' => getenv('LARDER_CLIENT_SECRET'),
        'redirect' => getenv('APP_URL') . '/login/larder/callback',
        'token' => env('LARDER_PERSONAL_TOKEN'),
    ]
    // TODO API base URL
    // TODO default API limit
];
