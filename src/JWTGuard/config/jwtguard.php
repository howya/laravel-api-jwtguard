<?php

return [

    'verification_alg' => 'RS256',
    'passport_oauth_server' => env('AUTH_SERVER'),
    'public_key_file' => storage_path('oauth-public.key'),
    'hydrate_user_uri' => '/api/v1.0/profile/'

];