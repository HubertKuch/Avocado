<?php

namespace Avocado\AvocadoApplication\Cache;

use Avocado\Application\Application;
use Avocado\AvocadoApplication\Attributes\Avocado;

#[Avocado]
class CacheMockedApplication {
    public static function init(): void {
        Application::run(CacheMockedApplication::class, dirname(__DIR__, 3));
    }
}