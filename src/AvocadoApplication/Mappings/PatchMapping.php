<?php

namespace AvocadoApplication\Mappings;

use Attribute;
use Avocado\HTTP\HTTPMethod;

#[Attribute(Attribute::TARGET_METHOD)]
class PatchMapping extends MethodMapping {
    public function __construct(string $endpoint) {
        parent::__construct($endpoint, HTTPMethod::INFO, []);
    }
}
