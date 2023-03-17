<?php

namespace Avocado\AvocadoORM\Attributes\Relations;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class JoinColumn {
    public function __construct(
        private string $table,
        private string $name,
        private string $referencedName = 'id'
    ) {}

    public function getName(): string {
        return $this->name;
    }

    public function getReferencedName(): string {
        return $this->referencedName;
    }

    public function getTable(): string {
        return $this->table;
    }
}