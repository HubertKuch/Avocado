<?php

namespace Avocado\AvocadoApplication\Attributes\Request;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class RequestStorageItem {

    public function __construct(private readonly string $name, private readonly mixed $defaultValue = null){}

    public function getName(): string {
        return $this->name;
    }

    public function getDefaultValue(): mixed {
        return $this->defaultValue;
    }
}