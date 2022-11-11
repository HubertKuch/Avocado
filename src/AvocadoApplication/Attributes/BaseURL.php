<?php

namespace AvocadoApplication\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class BaseURL {
    private string $url;

    public function __construct(string $url) {
        $this->url = $url;
    }

    public function get(): string {
        return $this->url;
    }
}
