<?php

return [
    'username'         => '',
    'password'         => '',
    'apiRefundUrl'     => 'https://refundservices.pec.ir/api/Refund/',
    'withoutVerifying' => 'false',
    'certificate'      => '',
    'certificateType'  => 'xml_file',// can be: xml_file, xml_string
    'callbackUrl'      => 'http://yoursite.com/path/to',
    'http_request'     => [
        'time_out'    => env('APP_HTTP_TIME_OUT', 10),
        'retry_times' => env('APP_HTTP_RETRY_TIMES', 10),
        'retry_sleep' => env('APP_HTTP_RETRY_SLEEP', 1000),// in millisecond unit
    ],
];
