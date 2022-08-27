<?php

namespace AvocadoApplication\Attributes;

use Attribute;
use Avocado\AvocadoApplication\DependencyInjection\Resourceable;

#[Attribute(Attribute::TARGET_CLASS)]
class Resource implements Resourceable {
    private ?string $targetResourceClass;
    private ?object $targetInstance;

    public function __construct(string $targetResourceClass = null, object $targetInstance = null) {
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
