<?php

namespace Avocado\AvocadoApplication\Attributes;

use Attribute;
use ReflectionMethod;
use ReflectionException;
use Avocado\AvocadoApplication\DependencyInjection\Resourceable;
use Avocado\AvocadoApplication\Exceptions\InvalidResourceException;

#[Attribute(Attribute::TARGET_METHOD)]
class Leaf implements Resourceable {
    private string $methodName;
    private string $leafName;
    private ReflectionMethod $reflectionMethod;
    private string $returnType;
    private object $resourceInstance;

    public function __construct(?string $name = "") {
        $this->leafName = $name;
    }

    /**
     * @throws InvalidResourceException
     */
    public function init(ReflectionMethod $method, Configuration $configuration): Leaf {
        $this->methodName = $method->getName();
        $this->reflectionMethod = $method;
        $configuration1 = $configuration;
        $this->leafName = $this->getLeafName();

        $returnTypes = $method->getReturnType()->getName();

        if (empty($returnTypes)) {
            throw new InvalidResourceException("Any Leaf must have to have exactly one non-primitive return type.");
        }

        $this->returnType = $this->reflectionMethod->getReturnType()->getName();
        try {
            $this->resourceInstance = $method->invoke($configuration1->getInstance());
        } catch (ReflectionException $e) {
            throw new InvalidResourceException("Any Leaf must have to have exactly one non-primitive return type.");
        }

        return $this;
    }

    private function getLeafName(): string {
        $reflection = $this->getReflectionMethod();

        $leafAttribute = $reflection->getAttributes(Leaf::class);

        /** @var $leaf Leaf */
        $leaf = $leafAttribute[0]->newInstance();

        return $leaf->getName();
    }

    /**
     * @throws InvalidResourceException
     */
    public static function instance(ReflectionMethod $method, Configuration $configuration): Leaf {
        return (new Leaf())->init($method, $configuration);
    }

    public function getMethodName(): string {
        return $this->methodName;
    }

    public function setMethodName(string $methodName): void {
        $this->methodName = $methodName;
    }

    public function getReflectionMethod(): ReflectionMethod {
        return $this->reflectionMethod;
    }

    public function getTargetResourceTypes(): array {
        return [$this->returnType];
    }

    public function getMainType(): string {
        return $this->returnType;
    }

    public function getName(): string {
        return $this->leafName;
    }

    public function getAlternativeName(): string {
        return ($this->getName()) ?? $this->getMethodName();
    }

    public function setReflectionMethod(ReflectionMethod $reflectionMethod): void {
        $this->reflectionMethod = $reflectionMethod;
    }

    public function getTargetInstance(): object {
        return $this->resourceInstance;
    }
}
