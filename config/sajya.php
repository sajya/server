<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Delimiter
    |--------------------------------------------------------------------------
    |
    | This value defines the delimiter used to separate class and method names
    | when specifying JSON-RPC method handlers. By default, "@" is used.
    |
    | Example:
    |
    | If you want to change the delimiter to dot, you can set this value
    | to ".".
    |
    | Please note that the delimiter should be a single character.
    | This can be useful if you have a different convention for
    | naming your methods.
    |
    */

    'delimiter' => '@',

    /*
    |--------------------------------------------------------------------------
    | Maximum Batch Size
    |--------------------------------------------------------------------------
    |
    | This value defines the maximum number of JSON-RPC requests allowed in
    | a single batch request. Adjust this according to your application's
    | requirements and server capabilities.
    |
    | Example:
    |
    | If you anticipate handling large batches of requests, you may need to
    | increase this value. To allow an unlimited number of requests in a
    | single batch, you can set this value to PHP_INT_MAX.
    |
    */

    'max_batch_size' => 30,

    /*
    |--------------------------------------------------------------------------
    | Encode Options
    |--------------------------------------------------------------------------
    |
    | This value defines the JSON encoding options used when encoding
    | JSON-RPC responses. Refer to the PHP json_encode documentation
    | for available options.
    |
    | Example:
    |
    | If you want to format the JSON response for better readability, you can
    | include the JSON_PRETTY_PRINT option. This will add indentation and
    | line breaks to the JSON output.
    |
    */

    'encode_options' => 0,
];
