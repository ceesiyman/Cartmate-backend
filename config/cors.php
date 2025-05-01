<?php

return [

'paths' => ['api/*', 'sanctum/csrf-cookie'],

'allowed_methods' => ['*'], // Allow all HTTP methods

'allowed_origins' => ['http://localhost:3000'],

'allowed_origins_patterns' => [],

'allowed_headers' => ['*'], // Allow all headers

'exposed_headers' => [],

'max_age' => 0,

'supports_credentials' => true,

];
