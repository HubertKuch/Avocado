<?php

namespace Avocado\AvocadoApplication\Attributes;

use Attribute;
use Avocado\AvocadoApplication\DependencyInjection\Resourceable;
use Avocado\Utils\AvocadoClassFinderUtil;
use Avocado\Utils\ReflectionUtils;
use AvocadoApplication\DependencyInjection\DependencyInjectionService;
use AvocadoApplication\DependencyInjection\Exceptions\ResourceNotFoundException;
use ReflectionClass;
use ReflectionException;
use ReflectionObject;
use Avocado\AvocadoApplication\Exceptions\ClassNotFoundException;
use Avocado\AvocadoApplication\Exceptions\InvalidResourceException;

#[Attribute(Attribute::TARGET_CLASS)]
class Configuration implements Resourceable {
    private string $targetClassName;
    private ReflectionClass $targetReflection;
    private ?object $instance;

    /**
     * @throws ClassNotFoundException
     */
    public function __construct(string $targetClassName = "", object $instance = null) {
        if ($targetClassName == "") {
            return;
        }

        $this->instance = $instance;

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
            if ($this->instance) {
                return $this->instance;
            }

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

    public function isConfigurationsProperties(): bool {
        return ReflectionUtils::getAttributeFromClass($this->targetReflection, ConfigurationProperties::class) !== null;
    }

    public function getConfigurationsPropertiesInstance(): ConfigurationProperties {
        /** @var $instance ConfigurationProperties */
        $instance = ReflectionUtils::getAttributeFromClass($this->targetReflection, ConfigurationProperties::class)->newInstance();

        $instance->setTargetClass($this->targetReflection);

        return $instance;
    }

    public function getTargetReflection(): ReflectionClass {
        return $this->targetReflection;
    }

    public function getTargetClassName(): string {
        return $this->targetClassName;
    }

    public function getTargetResourceTypes(): array {
        return [$this->getTargetClassName()];
    }

    public function getMainType(): string {
        return $this->targetClassName;
    }

    public function getTargetInstance(): object|null {
        try {
            $instance = $this->getInstance();
            DependencyInjectionService::inject(new ReflectionObject($instance), $instance);

            return $instance;
        } catch (ResourceNotFoundException) { return null; }
    }

    public function getAlternativeName(): string {
        return "";
    }
}
