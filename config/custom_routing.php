<?php
$routes = [
    'tg-admin' => 'trongate_administrators/login',
    'tg-admin/submit_login' => 'trongate_administrators/submit_login',
    /* Two versions in case you want to make wordle the default module */
    'api' => 'words/api',
    'wordle/api' => 'words/api',
];
define('CUSTOM_ROUTES', $routes);