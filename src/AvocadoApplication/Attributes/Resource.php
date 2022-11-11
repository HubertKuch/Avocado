<?php

namespace AvocadoApplication\Attributes;

use Attribute;
use Avocado\AvocadoApplication\DependencyInjection\Resourceable;

#[Attribute(Attribute::TARGET_CLASS)]
class Resource implements Resourceable {
    private ?array $resourceTypes;
    private ?string $mainType;
    private ?object $targetInstance;
    private ?string $alternativeName;

    public function __construct(?string $name = "", ?array $types = null, ?string $mainType = null, ?object $targetInstance = null) {
        $this->alternativeName = $name;
        $this->resourceTypes = $types;
        $this->targetInstance = $targetInstance;
        $this->mainType = $mainType;
    }

    public function getTargetResourceTypes(): array {
        return $this->resourceTypes;
    }

    public function getTargetInstance(): object {
        return $this->targetInstance;
    }

    public function getAlternativeName(): string {
        return $this->alternativeName;
    }

    public function getMainType(): string {
        return $this->mainType;
    }
}
