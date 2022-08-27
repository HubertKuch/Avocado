<?php

namespace AvocadoApplication\DependencyInjection;

use Avocado\Utils\ClassFinder;
use Avocado\AvocadoApplication\DependencyInjection\Resourceable;
use AvocadoApplication\Attributes\Autowired;
use AvocadoApplication\Attributes\Resource;
use AvocadoApplication\DependencyInjection\Exceptions\ResourceException;
use AvocadoApplication\DependencyInjection\Exceptions\ResourceNotFoundException;
use AvocadoApplication\DependencyInjection\Exceptions\TooMuchResourceConstructorParametersException;
use ReflectionClass;
use ReflectionException;
use ReflectionObject;
use ReflectionProperty;

class DependencyInjectionService {
    private const RESOURCE_NOT_FOUND_EXCEPTION = "`%s` resource was not found. Check if your resource is  annotated with `Resource` attribute.";
    private const CONSTRUCTOR_WAS_NOT_SET_RESOURCE_EXCEPTION = "Resource must have public constructor.";
    private const PUBLIC_CONSTRUCTOR_RESOURCE_EXCEPTION = "Resource must have to constructor.";
    private const TOO_MUCH_RESOURCE_PROPERTIES_EXCEPTION = "Resource constructor cannot have any parameters.";
    /* @var $resources Resourceable[] */
    private static array $resources = [];

    private static function getClassNamesOfResources(): array {
        return array_filter(array_map(fn($class) => $class->getName(), ClassFinder::getClasses()), function ($class) {
            try {
                $reflection = new ReflectionClass($class);
                $resourceAttributes = $reflection->getAttributes(Resource::class);

                return !empty($resourceAttributes);
            } catch (ReflectionException){ return false; }
        });
    }

    public static function addResource(Resourceable $resource): void {
        self::$resources[] = $resource;
    }

    /**
     * @return ReflectionProperty[]
     * @var $reflectionProperties ReflectionProperty[]
     */
    public static function getAutowiredProperties(array $reflectionProperties): array {
        return array_filter($reflectionProperties, fn($parameter) => !empty($parameter->getAttributes(Autowired::class)));
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

            return (new ReflectionClass($resourceName))->newInstance();
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
            self::$resources[] = new Resource($resource, self::newResourceInstance($resource));
        }
    }

    /**
     * @throws ResourceNotFoundException
     */
    public static function getResourceByType(string $autowiredClassPropertyType): Resourceable {
        $resource = array_filter(self::$resources, fn($resource) => $resource->getTargetResourceClass() == $autowiredClassPropertyType);
        $resource = key($resource) !== NULL ? $resource[key($resource)] : NULL;

        if (!$resource) {
            throw new ResourceNotFoundException(sprintf(self::RESOURCE_NOT_FOUND_EXCEPTION, $autowiredClassPropertyType));
        }

        return $resource;
    }

    /**
     * @throws TooMuchResourceConstructorParametersException
     * @throws ResourceNotFoundException|ResourceException
     */
    public static function inject(ReflectionObject $reflectionObject, object $object): void {
        $resources = self::getClassNamesOfResources();

        $autowiredClassProperties = DependencyInjectionService::getAutowiredProperties($reflectionObject->getProperties());

        self::createResources($resources);

        foreach ($autowiredClassProperties as $autowiredClassProperty) {
            $resourceType = $autowiredClassProperty->getType()->getName();
            $resource = self::getResourceByType($resourceType);

            $autowiredClassProperty->setValue($object, $resource->getTargetInstance());
        }
    }

    /**
     * @return Resourceable[]
     */
    public static function getResources(): array {
        return self::$resources;
    }
}
