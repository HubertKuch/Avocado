<?php

namespace Avocado\Tests\Unit\Application;

use Avocado\Application\Application;
use Avocado\AvocadoApplication\Attributes\Configuration;
use Avocado\AvocadoApplication\Exceptions\MissingAnnotationException;
use Avocado\HTTP\HTTPMethod;
use Avocado\HTTP\HttpTemplate;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 * */
class ApplicationTest extends TestCase {

    public function testExcludedClasses(): void {
        MockedApplication::init();

        $ref = new ReflectionClass(Application::class);

        $classes = $ref->getStaticPropertyValue("declaredClasses");

        self::assertFalse(in_array(TestClassToExclude::class, $classes));
    }

    public function testInjectingTwoLeafsOfTheSameType() {
        MockedApplication::init();

        $ref = new ReflectionClass(Application::class);

        /** @var $confs Configuration[] */
        $confs = $ref->getStaticPropertyValue("configurations");
        $matchedParentConfs = array_filter($confs, fn($conf) => $conf->getTargetClassName() === InjectedTwoLeafsOfTheSameType::class);

        /** @var $instance InjectedTwoLeafsOfTheSameType */
        $instance = $matchedParentConfs[key($matchedParentConfs)]->getTargetInstance();

        self::assertNotNull($instance->getTest());
        self::assertNotNull($instance->getTest2());
    }

    public function testGivenNotValidFilter_thenProcess_returnNothing() {
        HttpTemplate::mockPlainRequest(HTTPMethod::GET, "/avocado-test/");
        MockedApplicationToTestFilters::init();

        self::assertEmpty(ob_get_contents());
    }
}