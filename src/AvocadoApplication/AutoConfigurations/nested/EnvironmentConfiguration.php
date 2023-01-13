<?php

namespace Avocado\AvocadoApplication\AutoConfigurations\nested;

use AvocadoApplication\Environment\EnvironmentType;

class EnvironmentConfiguration {
    /**
     * @description Allowed cases [PRODUCTION, DEVELOPMENT]
     * */
    private string $type;
    private bool $throws;

    public function getEnvironment(): ?EnvironmentType {
        return EnvironmentType::tryFrom($this->type);
    }

    public function isThrows(): bool {
        return $this->throws;
    }
}