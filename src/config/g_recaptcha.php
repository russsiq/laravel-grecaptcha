<?php

return [
    'used' => true,
    'api_render' => 'https://www.google.com/recaptcha/api.js?render=',
    'api_verify' => 'https://www.google.com/recaptcha/api/siteverify',
    
    'site_key' => env('RECAPTCHA_SITE_KEY', 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'),
    'secret_key' => env('RECAPTCHA_SECRET_KEY', 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'),
    'score' => 0.5,
];
