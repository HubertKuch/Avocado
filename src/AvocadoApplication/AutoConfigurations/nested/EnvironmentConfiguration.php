<?php

namespace Avocado\AvocadoApplication\AutoConfigurations\nested;

use AvocadoApplication\Attributes\Nullable;
use AvocadoApplication\Environment\EnvironmentType;

class EnvironmentConfiguration {
    /**
     * @description Allowed cases [PRODUCTION, DEVELOPMENT]
     * */
    #[Nullable]
    private ?string $type = "DEVELOPMENT";
    /**
     * @description Is throws exception or return json representation.
     * */
    private bool $throws;

    /**
     * @description Returns environment type from application.{yaml,json} file
     * */
    public function getEnvironmentDefinedEnvironment(): ?EnvironmentType {
        return EnvironmentType::tryFrom($this->type);
    }

    /**
     * @description Similar to `getEnvironmentDefinedEnvironment` but return `DEVELOPMENT` type when was not specified
     * */
    public function getEnvironmentType(): EnvironmentType {
        return EnvironmentType::tryFrom($this->type) ?? EnvironmentType::DEVELOPMENT;
    }

    public function isThrows(): bool {
        return $this->throws;
    }
}