<?php

namespace Avocado\AvocadoORM\Attributes\Relations;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class OneToMany {
    public function __construct(
        private string $class
    ) {}

    public function getClass(): string {
        return $this->class;
    }
}