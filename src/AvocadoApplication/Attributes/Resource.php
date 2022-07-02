<?php

namespace AvocadoApplication\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Resource {
    private string $targetResourceClass;
    private object $targetInstance;

    public function __construct(string $targetResourceClass, object $targetInstance) {
        $this->targetResourceClass = $targetResourceClass;
        $this->targetInstance = $targetInstance;
    }

    public function getTargetResourceClass(): string {
        return $this->targetResourceClass;
    }

    public function getTargetInstance(): object {
        return $this->targetInstance;
    }
}
