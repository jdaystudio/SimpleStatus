<?php
/**
 * This returns some configuration options in an array
 * (all files in this project must be in the same folder)
 *
 * @author John Day jdayworkplace@gmail.com
 */

return [
    // url which points to this folder for these files
    'path' => '/private',

    // list your installed FPM php versions here (each will have a fpm status section)
    // and the url for its status endpoint (note: query params full&json will be appended)
    'php-versions' => [
        '8.3' => 'http://127.0.0.1/fpm-83-status',
        '7.4' => 'http://127.0.0.1/fpm-74-status',
    ],

    // list of available pages, remark out any you don't need
    'pages' => [
        "status",
        "notes",
        "phpinfo",
    ],

    // used when displaying dates and times in output tables
    'timezone' => 'UTC',
    'datetime-format' => 'D j M,  H:i:s',
    'timesince-format' => '%a Days, %H:%i:%s',
];