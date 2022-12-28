<?php

namespace Avocado\Tests\Unit\Application;

use Avocado\AvocadoApplication\Attributes\Configuration;
use Avocado\AvocadoApplication\Attributes\Leaf;
use AvocadoApplication\Attributes\Autowired;

#[Configuration]
class MockedConfigurationWithAutowiredConfiguration {

    #[Autowired]
    private ?MockConfigurationPropertiesClass $conf;

    public function __construct() {}

    #[Leaf]
    public function getConf(): ?MockConfigurationPropertiesClass {
        return $this->conf;
    }
}