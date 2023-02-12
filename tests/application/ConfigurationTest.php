<?php

namespace Avocado\Tests\Unit\Application;

use ReflectionClass;
use PHPUnit\Framework\TestCase;
use Avocado\Application\Application;
use Avocado\AvocadoApplication\Leafs\LeafManager;
use Avocado\AvocadoApplication\Attributes\Configuration;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 * */
class ConfigurationTest extends TestCase {

    /**
     * @runInSeparateProcess
     * */
    public function testGetConfigurations() {
        MockedApplication::init();

        $reflection = new ReflectionClass(Application::class);

        self::assertTrue($reflection->getStaticPropertyValue("configurations")[0] instanceof Configuration);
    }

    /**
     * @runInSeparateProcess
     * */
    public function testGetLeafByClass() {
        MockedApplication::init();

        $reflection = new ReflectionClass(Application::class);
        /** @var $leafManager LeafManager */
        $leafManager = $reflection->getStaticPropertyValue("leafManager");

        $resource = $leafManager->getLeafByClass(MockedLeafResource::class);

        self::assertTrue($resource instanceof MockedLeafResource);
    }

    /**
     * @runInSeparateProcess
     * */
    public function testGetLeafByName() {
        MockedApplication::init();

        $reflection = new ReflectionClass(Application::class);
        /** @var $leafManager LeafManager */
        $leafManager = $reflection->getStaticPropertyValue("leafManager");

        $resource = $leafManager->getLeafByName("mocked_rsc");

        self::assertTrue($resource instanceof MockedLeafResource);
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

    public function testParsingEmptyConfiguration() {
        MockedApplication::init();

        $conf = Application::getConfiguration()->getConfiguration(ConfigurationWithoutMockedProperties::class);

        self::assertNotNull($conf);
        self::assertNull($conf->getTest());
    }

    public function testParsingEnum() {
        MockedApplication::init();

        $conf = Application::getConfiguration()->getConfiguration(MockConfigurationPropertiesClass::class);

        $value = $conf -> getEnumToParse();

        self::assertEquals(TestEnumToParse::TEST, $value);
    }
}
