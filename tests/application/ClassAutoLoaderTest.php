<?php

namespace Avocado\Tests\Unit\Application;

use Avocado\Application\Application;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 * */
class ClassAutoLoaderTest extends TestCase {

    /**
     * @runInSeparateProcess
     * */
    public function testGettingClassesNames() {
        $_SERVER['REQUEST_METHOD'] = "GET";
        Application::run(dirname(__DIR__, 2));

        $appReflection = new ReflectionClass(Application::class);

        $getDeclaredClasses = $appReflection -> getMethod("getDeclaredClasses");

        $classes = $getDeclaredClasses->invoke(null);

        self::assertNotEmpty($classes);
    }
}
