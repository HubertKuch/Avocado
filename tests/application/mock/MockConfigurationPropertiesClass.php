<?php

namespace Avocado\Tests\Unit\Application;

use Avocado\AvocadoApplication\Attributes\Configuration;
use Avocado\AvocadoApplication\Attributes\ConfigurationProperties;

#[Configuration]
#[ConfigurationProperties(prefix: "mocked-configuration")]
class MockConfigurationPropertiesClass {
    private string $test;
    private string $test2;
    private ?AnotherObject $anotherObject;
    private array $testArray;

    public function getTestArray(): array {
        return $this->testArray;
    }
}