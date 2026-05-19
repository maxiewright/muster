<?php

declare(strict_types=1);

$config = require base_path('vendor/fruitcake/laravel-debugbar/config/debugbar.php');

$config['inject'] = (bool) env('DEBUGBAR_INJECT', ! (bool) env('CSP_ENABLED', true));

return $config;
