<?php

namespace AvocadoApplication\Attributes;

use Attribute;
use phpDocumentor\Reflection\Utils;
use Avocado\AvocadoApplication\DependencyInjection\Resourceable;

#[Attribute(Attribute::TARGET_CLASS)]
class Resource implements Resourceable {
    private ?string $targetResourceClass;
    private ?object $targetInstance;
    private ?string $alternativeName;

    public function __construct(?string $name = "", string $targetResourceClass = null, object $targetInstance = null) {
        $this->alternativeName = $name;
        $this->targetResourceClass = $targetResourceClass;
        $this->targetInstance = $targetInstance;
    }

    public function getTargetResourceClass(): string {
        return $this->targetResourceClass;
    }

    public function getTargetInstance(): object {
        return $this->targetInstance;
    }

    public function getAlternativeName(): string {
        return $this->alternativeName;
    }
}
