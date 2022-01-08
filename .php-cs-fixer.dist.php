<?php

declare(strict_types=1);

use Palmtree\PhpCsFixerConfig\Config;

$config = new Config();

$config
    ->getFinder()
    ->in(__DIR__ . '/src')
    ->append([__FILE__])
;

return $config;
