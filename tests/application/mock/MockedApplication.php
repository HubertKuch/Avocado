<?php

namespace Avocado\Tests\Unit\Application;

use Avocado\Application\Application;
use Avocado\AvocadoApplication\Attributes\Avocado;
use Avocado\AvocadoApplication\Attributes\Exclude;

#[Avocado]
#[Exclude([TestClassToExclude::class])]
class MockedApplication {

    public static function init(): void {
        Application::run(dirname(__DIR__, 2));
    }
}
