<?php

namespace Avocado\AvocadoApplication\Attributes;

use Attribute;
use Avocado\AvocadoApplication\Exceptions\InvalidResourceException;
use ReflectionException;
use ReflectionMethod;

#[Attribute(Attribute::TARGET_METHOD)]
class Leaf {
    private string $methodName;
    private string $leafName;
    private ReflectionMethod $reflectionMethod;
    private string $returnType;
    private object $resourceInstance;
    private Configuration $configuration;

    public function __construct(?string $name = "") {}

    /**
     * @throws InvalidResourceException
     */
    public function init(ReflectionMethod $method, Configuration $configuration): Leaf {
        $this->methodName = $method->getName();
        $this->reflectionMethod = $method;
        $this->configuration = $configuration;
        $this->leafName = $this->getLeafName();

        $returnTypes = $method->getReturnType()->getName();

        if (empty($returnTypes)) {
            throw new InvalidResourceException("Any Leaf must have to have exactly one non-primitive return type.");
        }

        $this->returnType = $this->reflectionMethod->getReturnType()->getName();
        try {
            $this->resourceInstance = $method->invoke($this->configuration->getInstance());
        } catch (ReflectionException $e) {
            throw new InvalidResourceException("Any Leaf must have to have exactly one non-primitive return type.");
        }

        return $this;
    }

    private function getLeafName(): string {
        $reflection = $this->getReflectionMethod();

        $leafAttribute = $reflection->getAttributes(Leaf::class);
        $args = $leafAttribute[0]->getArguments();

        if (empty($args)) {
            return "";
        }

        return key_exists('name', $args) ? $args['name'] : $args[0];
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

    public function getType(): string {
        return $this->returnType;
    }

    public function getName(): string {
        return $this->leafName;
    }

    public function setReflectionMethod(ReflectionMethod $reflectionMethod): void {
        $this->reflectionMethod = $reflectionMethod;
    }

    public function getResourceInstance(): object {
        return $this->resourceInstance;
    }
}
