<?php

namespace AvocadoApplication\AutoConfigurations;

use Avocado\AvocadoApplication\Attributes\Configuration;
use Avocado\AvocadoApplication\Attributes\ConfigurationProperties;
use AvocadoApplication\Environment\EnvironmentType;

#[Configuration]
#[ConfigurationProperties("avocado")]
class AvocadoEnvironmentConfiguration {

    /**
     * Allowed cases [PRODUCTION, DEVELOPMENT]
     * */
    private string $environment;
    private bool $throws;

    public function getEnvironment(): ?EnvironmentType {
        return EnvironmentType::tryFrom($this->environment);
    }

    public function isThrows(): bool {
        return $this->throws;
    }
}