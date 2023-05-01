<?php

declare(strict_types=1);

use Palmtree\PhpCsFixerConfig\Config;

$config = new Config();

$config
    ->setCacheFile(__DIR__ . '/var/.php-cs-fixer.cache')
    ->getFinder()
    ->in(__DIR__ . '/src')
    ->append([__FILE__])
;

return $config;
