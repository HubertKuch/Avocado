<?php

namespace Avocado\Application;

use AvocadoApplication\Attributes\BaseURL;
use AvocadoApplication\Mappings\MethodMapping;
use ReflectionClass;
use ReflectionMethod;

class Controller {
    private string $targetClassName;
    private ReflectionClass $targetReflection;
    private array $mappings;
    private BaseURL $baseURL;

    public function __construct(string $targetClassName = null, ReflectionClass $targetReflection = null) {
        if (!$targetReflection || !$targetClassName) {
            return;
        }


        $this->targetClassName = $targetClassName;
        $this->targetReflection = $targetReflection;

        $this->baseURL = $this->getBaseUrl();

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

    private function getBaseUrl(): BaseURL {
        $baseUrlAttribute = $this->targetReflection->getAttributes(BaseURL::class)[0] ?? null;
        $baseUrl = "";

        if ($baseUrlAttribute) {
            $baseUrl = $baseUrlAttribute->getArguments()[0];
        }

        return new BaseURL($baseUrl);
    }

    private function getUrl(ReflectionMethod $reflectionMethod): string {
        return $this->baseURL->get().MethodMapping::getEndpointFromReflectionMethod($reflectionMethod);
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
                $this->getUrl($reflectionMethod),
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
