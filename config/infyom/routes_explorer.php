<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enable Routes Explorer route
    |--------------------------------------------------------------------------
    |
    | Make sure, either you protect this route by overriding it with some middleware
    | or make it false in production environment
    |
    */

    'enable_explorer' => true,

    /*
    |--------------------------------------------------------------------------
    | Routes Explorer route
    |--------------------------------------------------------------------------
    |
    | On this url, routes explorer will be displayed
    |
    */

    'route' => 'admin/route-list',

    /*
    |--------------------------------------------------------------------------
    | Routes Explorer view file name
    |--------------------------------------------------------------------------
    |
    | this view file will be used to display a view
    |
    */

    'view' => 'route-list',

    /*
    |--------------------------------------------------------------------------
    | Data Collectors
    |--------------------------------------------------------------------------
    |
    | Various data collectors to collect data
    |
    */

    'collections' => [

        // collect api calls count
        'api_calls_count' => false,

    ],
];
