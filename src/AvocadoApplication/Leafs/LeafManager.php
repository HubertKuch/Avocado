<?php

namespace Avocado\AvocadoApplication\Leafs;

use Avocado\AvocadoApplication\Attributes\Leaf;
use Avocado\AvocadoApplication\Attributes\Configuration;
use Avocado\AvocadoApplication\Exceptions\InvalidResourceException;

class LeafManager {
    /** @var $leafs Leaf[] */
    private array $leafs;

    public function __construct(array $leafs) {
        $this->leafs = $leafs;
    }

    /**
     * @param Configuration[] $configurations
     * @return LeafManager
     * @throws InvalidResourceException
     */
    public static function ofConfigurations(array $configurations): LeafManager {
        $leafs = [];

        foreach ($configurations as $configuration) {
            $leafs = [...$leafs, ...$configuration->getLeafs($configuration)];
        }

        return new LeafManager($leafs);
    }

    /**
     * @throws InvalidResourceException
     */
    public function getLeafByClass(string $class): object {
        /** @var $filteredLeafs Leaf[]*/
        $filteredLeafs = array_filter($this->leafs, fn($leaf) => in_array($class, $leaf->getTargetResourceTypes()));

        if (empty($filteredLeafs)) {
            throw new InvalidResourceException("Leaf of type `$class` is not exists.");
        }

        return $filteredLeafs[key($filteredLeafs)]->getTargetInstance();
    }

    /**
     *
     * @throws InvalidResourceException
     */
    public function getLeafByName(string $name): object {
        /** @var $filteredLeafs Leaf[]*/
        $filteredLeafs = array_filter($this->leafs, fn($leaf) => $leaf->getName() === $name);

        if (empty($filteredLeafs)) {
            throw new InvalidResourceException("Leaf `$name` is not exists.");
        }

        return $filteredLeafs[key($filteredLeafs)]->getTargetInstance();
    }

    /**
     * @return Leaf[]
    */
    public function getLeafs(): array {
        return $this->leafs;
    }
}
