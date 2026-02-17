<?php

return [
    'csp_enabled' => (bool) env('CSP_ENABLED', true),
    'csp_reverb_host' => env('REVERB_HOST'),
    'csp_reverb_port' => env('REVERB_PORT'),
];
