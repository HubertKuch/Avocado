<?php

namespace Avocado\Tests\Unit\Application;

use Avocado\AvocadoApplication\Attributes\Configuration;
use Avocado\AvocadoApplication\Attributes\ConfigurationProperties;

#[Configuration]
#[ConfigurationProperties("without-data")]
class ConfigurationWithoutMockedProperties {
    private ?string $test = null;

    public function getTest(): ?string {
        return $this->test;
    }
}