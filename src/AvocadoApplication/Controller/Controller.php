<?php

namespace Avocado\Application;

use ReflectionClass;
use ReflectionMethod;
use ReflectionObject;
use ReflectionException;
use AvocadoApplication\Attributes\BaseURL;
use AvocadoApplication\Mappings\MethodMapping;
use AvocadoApplication\DependencyInjection\DependencyInjectionService;
use AvocadoApplication\DependencyInjection\Exceptions\ResourceException;
use AvocadoApplication\DependencyInjection\Exceptions\ResourceNotFoundException;
use AvocadoApplication\DependencyInjection\Exceptions\TooMuchResourceConstructorParametersException;

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

    public function getTargetReflection(): ReflectionClass {
        return $this->targetReflection;
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
            return !empty(array_filter($method->getAttributes(), fn($atr) => MethodMapping::isMethodMapping($atr)));
        });

        return array_map(/**
         * @throws ResourceNotFoundException
         * @throws ResourceException
         * @throws TooMuchResourceConstructorParametersException
         */ function ($reflectionMethod) {
            return new MethodMapping(
                $this->getUrl($reflectionMethod),
                MethodMapping::getHTTPMethodFromReflectionMethod($reflectionMethod),
                MethodMapping::getCallbackFromReflectionMethod($reflectionMethod, $this->getInstance())
            );
        }, $mappings);
    }

    public function addMapping(MethodMapping $mapping): Controller {
        $this->mappings[] = $mapping;
        return $this;
    }

    public static function isController(ReflectionClass $reflection): bool {
        $attributes = $reflection->getAttributes();

        foreach ($attributes as $attribute) {
            try {
                $parentClass = (new ReflectionClass($attribute->getName()))?->getParentClass();

                if ($parentClass){
                    return $parentClass->getName() === Controller::class;
                }
            } catch (ReflectionException) {}
        }

        return false;
    }

    public static function isRestController(ReflectionClass $reflection): bool {
        return !empty($reflection->getAttributes(RestController::class));
    }

    /**
     * @throws ResourceException
     * @throws ResourceNotFoundException
     * @throws TooMuchResourceConstructorParametersException
     */
    private function getInstance(): ?object {
        try {
            $classReflection = $this->targetReflection;
            $controllerObject = $classReflection->newInstanceWithoutConstructor();

            DependencyInjectionService::inject(new ReflectionObject($controllerObject), $controllerObject);

            return $controllerObject;
        } catch (ReflectionException) { return null; }
    }
}
