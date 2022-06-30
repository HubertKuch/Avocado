<?php

namespace Avocado\Application;

use AvocadoApplication\Mappings\MethodMapping;
use ReflectionClass;

class Controller {
    private string $targetClassName;
    private ReflectionClass $targetReflection;
    private array $mappings;

    public function __construct(string $targetClassName = null, ReflectionClass $targetReflection = null) {
        if (!$targetReflection || !$targetClassName) {
            return;
        }

        $this->targetClassName = $targetClassName;
        $this->targetReflection = $targetReflection;
        $this->mappings = $this->setMappings();
    }

    public function getTargetClassName(): string {
        return $this->targetClassName;
    }

    public function setTargetClassName(string $targetClassName): Controller {
        $this->targetClassName = $targetClassName;
        return $this;
    }

    public function getTargetReflection(): ReflectionClass {
        return $this->targetReflection;
    }

    public function setTargetReflection(ReflectionClass $targetReflection): Controller {
        $this->targetReflection = $targetReflection;
        return $this;
    }

    public function getMappings(): array {
        return $this->mappings;
    }

    public function setMappings(): array {
        $reflection = $this->targetReflection;
        $classMethods = $reflection->getMethods();
        $mappings = array_filter($classMethods, function($method) {
            $attributes = $method->getAttributes();

            return !empty(array_filter($attributes, fn($atr) => MethodMapping::isMethodMapping($atr)));
        });

        $mappings = array_map(function ($reflectionMethod) {
            $mapping =  new MethodMapping(
                MethodMapping::getEndpointFromReflectionMethod($reflectionMethod),
                MethodMapping::getHTTPMethodFromReflectionMethod($reflectionMethod),
                MethodMapping::getCallbackFromReflectionMethod($reflectionMethod)
            );

            return $mapping;
        }, $mappings);

        return $mappings;
    }

    public function addMapping(MethodMapping $mapping): Controller {
        $this->mappings[] = $mapping;
        return $this;
    }

    public static function isController(ReflectionClass $reflection): bool {
        $attributes = $reflection->getAttributes();

        foreach ($attributes as $attribute) {
            $parentClass = (new ReflectionClass($attribute->getName()))?->getParentClass();

            if ($parentClass){
                return $parentClass->getName() === Controller::class;
            }
        }

        return false;
    }

    public static function isRestController(ReflectionClass $reflection): bool {
        return !empty($reflection->getAttributes(RestController::class));
    }
}
