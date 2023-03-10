<?php

namespace Avocado\Tests\Unit;

use Avocado\Application\Application;
use Avocado\AvocadoApplication\Attributes\Avocado;

#[Avocado]
class MockedApplicationWithNullableProperties {

    public static function run(): void {
        Application::run(static::class, dirname(__DIR__, 4));
    }

}