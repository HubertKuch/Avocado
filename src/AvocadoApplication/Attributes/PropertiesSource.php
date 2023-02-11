<?php

namespace Avocado\AvocadoApplication\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class PropertiesSource {
    public function __construct(private string $path) {}

    public function getPath(): string {
        return $this->path;
    }

}