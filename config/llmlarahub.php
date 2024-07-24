<?php

return [
    'email_name' => env('LARALAMMA_EMAIL_NAME', 'assistant'),
    'domain' => env('LARALAMMA_DOMAIN', 'larallama.io'),
    'collection' => [
        'system_prompt' => 'This is a collection of data the user has imported that they will
        ask questions about. The description they gave for this collection is',
    ],
    'check_system_email' => env('LARALAMMA_CHECK_EMAIL', true),
];
