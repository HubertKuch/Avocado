<?php

namespace Avocado\AvocadoApplication\Middleware;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD|Attribute::TARGET_CLASS)]
class BeforeRoute {

    /**
     * @param array<array<string>> $before Array of callbacks like [["UserService", "requiringLoggedIn"], ["NextService", "nextAction"]]
     * */
    public function __construct(private readonly array $before) {}

    public function getCallbacks(): array {
        return $this->before;
    }

}