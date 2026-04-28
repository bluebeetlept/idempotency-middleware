<?php

declare(strict_types = 1);

use Eufaturo\CodingStandards\Config\EufaturoEcsConfig;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->paths([
        __DIR__.'/src',
        __DIR__.'/tests',
        __DIR__.'/ecs.php',
        __DIR__.'/rector.php',
    ]);

    EufaturoEcsConfig::setup($ecsConfig);
};
