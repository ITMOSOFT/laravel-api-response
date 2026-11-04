<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default response messages
    |--------------------------------------------------------------------------
    */
    'messages' => [
        'success' => 'Request completed successfully.',
        'error' => 'The request could not be completed.',
        'created' => 'Resource created successfully.',
        'validation' => 'The given data was invalid.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Response envelope
    |--------------------------------------------------------------------------
    | Set a field to false to omit it from the JSON response. The success,
    | message, data, errors and meta fields are enabled by default.
    */
    'fields' => [
        'success' => true,
        'message' => true,
        'data' => true,
        'errors' => true,
        'meta' => true,
    ],

    'pagination' => [
        'include_links' => true,
    ],
];
