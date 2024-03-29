<?php

namespace Avocado\Tests\Unit\Application;

use Avocado\Application\Application;
use Avocado\AvocadoApplication\Attributes\Avocado;
use Avocado\AvocadoApplication\Attributes\Exclude;
use Avocado\Tests\Unit\TestNotValidFilter;

#[Avocado]
#[Exclude([TestClassToExclude::class, MockedLeafController::class, TestNotValidFilter::class])]
class MockedApplication {

    public static function init(): void {
        Application::run(MockedApplication::class, dirname(__DIR__, 3));
    }
}
