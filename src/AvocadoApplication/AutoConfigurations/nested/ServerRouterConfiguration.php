<?php

namespace Avocado\AvocadoApplication\AutoConfigurations\nested;

use Avocado\AvocadoRouter\MatchingStrategy;

class ServerRouterConfiguration {
    /**
     * @description Allowed options SELF, URI. When `SELF` is set avocado router matches routes by $_SERVER['PHP_SELF'] but if you use `URI` matching will be able by $_SERVER['REQUEST_URI'] global variable.
     * */
    private string $matchingStrategy = "SELF";

    public function getMatchingStrategy(): MatchingStrategy {
        return MatchingStrategy::from($this->matchingStrategy);
    }
}