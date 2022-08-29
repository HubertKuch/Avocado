<?php

namespace Avocado\AvocadoApplication\Attributes;

use Attribute;
use ReflectionClass;
use ReflectionException;
use Avocado\AvocadoApplication\Exceptions\ClassNotFoundException;
use Avocado\AvocadoApplication\Exceptions\InvalidResourceException;

#[Attribute(Attribute::TARGET_CLASS)]
class Configuration {
    private string $targetClassName;
    private ReflectionClass $targetReflection;

    /**
     * @throws ClassNotFoundException
     */
    public function __construct(string $targetClassName = "") {
        if ($targetClassName == "") {
            return;
        }

        try {
            $this->targetClassName = $targetClassName;
            $this->targetReflection = new ReflectionClass($targetClassName);
        } catch (ReflectionException) {
            throw new ClassNotFoundException("Class `$targetClassName` was not found.");
        }
    }

    public static function isConfiguration(string $class): bool {
        try {
            $reflection = new ReflectionClass($class);
            $configurationAnnotations = $reflection->getAttributes(Configuration::class);

            return !empty($configurationAnnotations);
        } catch (ReflectionException) {
            return false;
        }
    }

    /**
     * @throws InvalidResourceException
     */
    public function getInstance(): object {
        try {
            return $this->targetReflection->newInstance();
        } catch (ReflectionException) {
            throw new InvalidResourceException("Configuration cannot have any constructor parameters, only public constructor without parameters.");
        }
    }

    /**
     * @return Leaf[]
     * @throws InvalidResourceException
     */
    public function getLeafs(Configuration $configuration): array {
        $methods = $this->targetReflection->getMethods();

        return array_map(
            fn($method) => Leaf::instance($method, $configuration),
            array_filter(
                $methods,
                fn($method) => !empty($method->getAttributes(Leaf::class))
            )
        );
    }
}
