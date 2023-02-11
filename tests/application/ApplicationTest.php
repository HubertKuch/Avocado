<?php

namespace Avocado\Tests\Unit\Application;

use Avocado\Application\Application;
use Avocado\AvocadoApplication\Attributes\Configuration;
use Avocado\AvocadoApplication\Exceptions\MissingAnnotationException;
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

    public function testExcludingAvocadoTestsFromProductionApplication(): void {
        $this->expectException(MissingAnnotationException::class);
        $_ENV['AVOCADO_ENVIRONMENT'] = "PRODUCTION";

        MockedApplication::init();
    }

    public function testParseConfigurationArray() {
        MockedApplication::init();
        $applicationConfiguration = Application::getConfiguration();
        $conf = $applicationConfiguration->getConfiguration(MockConfigurationPropertiesClass::class);

        self::assertNotEmpty($conf->getTestArray());
    }

    public function testAutowiringConfigurationInAnotherConfiguration() {
        MockedApplication::init();

        $ref = new ReflectionClass(Application::class);

        /** @var $confs Configuration[] */
        $confs = $ref->getStaticPropertyValue("configurations");
        $matchedParentConfs = array_filter($confs, fn($conf) => $conf->getTargetClassName() === MockedConfigurationWithAutowiredConfiguration::class);
        /** @var $conf MockedConfigurationWithAutowiredConfiguration */
        $conf = $matchedParentConfs[key($matchedParentConfs)]->getTargetInstance();

        self::assertNotNull($conf->getConf());
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

    public function testParsingEmptyConfiguration() {
        MockedApplication::init();

        $conf = Application::getConfiguration()->getConfiguration(ConfigurationWithoutMockedProperties::class);

        self::assertNotNull($conf);
        self::assertNull($conf->getTest());
    }

}