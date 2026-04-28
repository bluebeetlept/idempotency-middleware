<?php

declare(strict_types = 1);

use Eufaturo\CodingStandards\Config\EufaturoRectorConfig;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->disableParallel();

    EufaturoRectorConfig::setup($rectorConfig);

    $rectorConfig->paths([
        __DIR__.'/src',
        __DIR__.'/tests',
        __DIR__.'/ecs.php',
        __DIR__.'/rector.php',
    ]);
};
