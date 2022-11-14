<?php

namespace AvocadoApplication\DependencyInjection;

use Avocado\Application\Application;
use Exception;
use ReflectionClass;
use ReflectionObject;
use ReflectionProperty;
use ReflectionException;
use Avocado\Utils\ClassFinder;
use Avocado\Utils\ReflectionUtils;
use AvocadoApplication\Attributes\Resource;
use AvocadoApplication\Attributes\Autowired;
use Avocado\AvocadoApplication\DependencyInjection\Resourceable;
use AvocadoApplication\DependencyInjection\Exceptions\ResourceException;
use AvocadoApplication\DependencyInjection\Exceptions\ResourceNotFoundException;
use AvocadoApplication\DependencyInjection\Exceptions\TooMuchResourceConstructorParametersException;

class DependencyInjectionService {
    private const RESOURCE_NOT_FOUND_EXCEPTION = "`%s` resource was not found. Check if your resource is  annotated with `Resource` attribute.";
    private const CONSTRUCTOR_WAS_NOT_SET_RESOURCE_EXCEPTION = "Resource must have public constructor.";
    private const PUBLIC_CONSTRUCTOR_RESOURCE_EXCEPTION = "Resource must have to constructor.";
    private const TOO_MUCH_RESOURCE_PROPERTIES_EXCEPTION = "Resource constructor cannot have any parameters.";
    /* @var $resources Resourceable[] */
    private static array $resources = [];

    /**
     * @throws ResourceNotFoundException
     * @throws ResourceException
     * @throws TooMuchResourceConstructorParametersException
     */
    public static function init(): void {
        $resources = self::getClassNamesOfResources();

        self::createResources($resources);
    }

    private static function getClassNamesOfResources(): array {
        $classes = ClassFinder::getClasses();

        $onlyNames = array_map(fn($class) => $class->getName(), $classes);

        $uniqueNames = array_unique($onlyNames);

        return array_filter($uniqueNames, function ($class) {
            try {
                $reflection = ClassFinder::getClassReflectionByName($class);
                $resourceAttributes = $reflection->getAttributes(Resource::class);

                return !empty($resourceAttributes);
            } catch (ReflectionException){ return false; }
        });
    }

    public static function addResource(Resourceable $resource): void {
        self::$resources[] = $resource;
    }

    /**
     * @return Autowired[]
     * @var $reflectionProperties ReflectionProperty[]
     */
    public static function getAutowiredProperties(array $reflectionProperties): array {
        $properties = array_filter($reflectionProperties, fn($parameter) => !empty($parameter->getAttributes(Autowired::class)));

        return array_map(function ($property) {
            $autowiredAttributes = $property->getAttributes(Autowired::class);

            /** @var $autowired Autowired */
            $autowired = $autowiredAttributes[key($autowiredAttributes)]->newInstance();

            return new Autowired($autowired->getAutowiredResourceName(), $property);
        }, $properties);
    }

    /**
     * @throws TooMuchResourceConstructorParametersException
     * @throws ResourceException
     * @throws ResourceNotFoundException
     */
    private static function validateResourceConstructor(string $resourceClass): void {
        if (!class_exists($resourceClass)) {
            throw new ResourceNotFoundException(sprintf(self::RESOURCE_NOT_FOUND_EXCEPTION, $resourceClass));
        }

        $resourceReflection = new ReflectionClass($resourceClass);

        if (!$resourceReflection->getConstructor()) {
            throw new ResourceException(self::CONSTRUCTOR_WAS_NOT_SET_RESOURCE_EXCEPTION);
        }

        if (!$resourceReflection->getConstructor()->isPublic()) {
            throw new ResourceException(self::PUBLIC_CONSTRUCTOR_RESOURCE_EXCEPTION);
        }

        if ($resourceReflection->getConstructor()->getNumberOfParameters() != 0) {
            throw new TooMuchResourceConstructorParametersException(self::TOO_MUCH_RESOURCE_PROPERTIES_EXCEPTION);
        }
    }

    /**
     * @throws ResourceNotFoundException
     * @throws TooMuchResourceConstructorParametersException
     */
    private static function newResourceInstance(string $resourceName): object {
        try {
            if (!class_exists($resourceName)) {
                throw new ResourceNotFoundException(sprintf(self::RESOURCE_NOT_FOUND_EXCEPTION, $resourceName));
            }

            $instance = (new ReflectionClass($resourceName))->newInstance();;

            return $instance;
        } catch (ReflectionException){
            throw new TooMuchResourceConstructorParametersException(self::TOO_MUCH_RESOURCE_PROPERTIES_EXCEPTION);
        }
    }

    /**
     * @throws ResourceNotFoundException
     * @throws TooMuchResourceConstructorParametersException
     * @throws ResourceException
     */
    private static function createResources(array $resources): void {
        foreach ($resources as $resource) {
            self::validateResourceConstructor($resource);

            $ref = ClassFinder::getClassReflectionByName($resource);

            $resourceAttr = ReflectionUtils::getAttributeFromClass($ref, Resource::class);
            /** @var $resourceAttrInstance Resource*/
            $resourceAttrInstance = $resourceAttr->newInstance();
            $instance = self::newResourceInstance($resource);
            $types = ReflectionUtils::getAllTypes($instance);

            self::$resources[] = new Resource($resourceAttrInstance->getAlternativeName(), $types, $resource, $instance);
        }

        foreach (self::$resources as $resource) {
            self::inject(new ReflectionObject($resource->getTargetInstance()), $resource->getTargetInstance());
        }
    }

    public static function getResourceByType(string $autowiredClassPropertyType): Resourceable|null {
        $resource = array_filter(self::$resources, fn($resource) => in_array($autowiredClassPropertyType, $resource->getTargetResourceTypes()));

        return key($resource) !== NULL ? $resource[key($resource)] : NULL;
    }

    /**
     * @throws ResourceNotFoundException
     */
    public static function inject(ReflectionObject $reflectionObject, object $object): void {
        $autowiredClassProperties = DependencyInjectionService::getAutowiredProperties($reflectionObject->getProperties());

        foreach ($autowiredClassProperties as $autowiredClassProperty) {
            $resourceType = $autowiredClassProperty->getReflectionProperty()->getType()->getName();
            $resource = self::getResourceByType($resourceType);

            if ($autowiredClassProperty->getAutowiredResourceName() != "") {
                $resource = self::getResourceByName($autowiredClassProperty->getAutowiredResourceName());
            }

            if (!$resource) {
                throw new ResourceNotFoundException(sprintf(self::RESOURCE_NOT_FOUND_EXCEPTION, ``));
            }

            $autowiredClassProperty->getReflectionProperty()->setValue($object, $resource->getTargetInstance());
        }
    }

    /**
     * @return Resourceable[]
     */
    public static function getResources(): array {
        return self::$resources;
    }

    public static function getResourceByName(string $name): ?Resourceable {
        $resource = array_filter(self::$resources, fn($resource) => $resource->getAlternativeName() == $name);
        $resource = key($resource) !== NULL ? $resource[key($resource)] : NULL;

        return $resource ?? NULL;
    }
}
