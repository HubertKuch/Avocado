<?php

namespace Avocado\Tests\Unit\Application;

use Avocado\Application\Application;
use Avocado\AvocadoApplication\Attributes\Avocado;
use Avocado\AvocadoApplication\Attributes\Exclude;

#[Avocado]
#[Exclude([MockedApplication::class])]
class MockedApplicationToTestFilters {
    public static function init(): void {
        Application::run(static::class, dirname(__DIR__, 3));
    }
}