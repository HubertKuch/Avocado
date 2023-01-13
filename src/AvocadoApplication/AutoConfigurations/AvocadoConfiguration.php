<?php

namespace AvocadoApplication\AutoConfigurations;

use Avocado\AvocadoApplication\Attributes\Configuration;
use Avocado\AvocadoApplication\Attributes\ConfigurationProperties;
use Avocado\AvocadoApplication\AutoConfigurations\nested\EnvironmentConfiguration;
use Avocado\AvocadoApplication\AutoConfigurations\nested\ServerRouterConfiguration;

#[Configuration]
#[ConfigurationProperties("avocado")]
class AvocadoConfiguration {

    private EnvironmentConfiguration $environment;
    private ServerRouterConfiguration $router;

    public function getEnvironmentConfiguration(): EnvironmentConfiguration {
        return $this->environment;
    }

    public function getServerRouterConfiguration(): ServerRouterConfiguration {
        return $this->router;
    }
}