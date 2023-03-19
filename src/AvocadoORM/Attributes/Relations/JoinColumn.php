<?php

namespace Avocado\AvocadoORM\Attributes\Relations;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class JoinColumn {
    public function __construct(
        private string $table,
        private string $name,
        private string $referencesTo = 'id'
    ) {}

    public function getName(): string {
        return $this->name;
    }

    public function getReferencesTo(): string {
        return $this->referencesTo;
    }

    public function getTable(): string {
        return $this->table;
    }
}