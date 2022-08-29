<?php

namespace Avocado\Application;

use Exception;
use ReflectionClass;
use ReflectionException;
use Avocado\HTTP\HTTPMethod;
use Avocado\Utils\ClassFinder;
use Avocado\Router\AvocadoRouter;
use Avocado\Utils\ReflectionUtils;
use Avocado\DataSource\DataSource;
use Avocado\ORM\AvocadoORMSettings;
use AvocadoApplication\Mappings\MethodMapping;
use Avocado\AvocadoApplication\Leafs\LeafManager;
use Avocado\AvocadoApplication\Attributes\Exclude;
use Avocado\AvocadoApplication\Attributes\Avocado;
use Avocado\AvocadoApplication\Attributes\Configuration;
use Avocado\AvocadoApplication\ApplicationExceptionsAdvisor;
use Avocado\AvocadoApplication\Exceptions\ClassNotFoundException;
use Avocado\AvocadoApplication\Exceptions\MissingAnnotationException;
use AvocadoApplication\DependencyInjection\DependencyInjectionService;

final class Application {
    private static array $declaredClasses = [];
    /** @var $configurations Configuration[] */
    private static array $configurations = [];
    private static array $controllers = [];
    private static array $restControllers = [];
    private static LeafManager $leafManager;
    private static DataSource $dataSource;
    private static Avocado $mainClass;

    public static final function run(string $dir): void {
        try {
            self::$declaredClasses = array_map(fn($class) =>
                $class->getName(), ClassFinder::getDeclaredClasses($dir)
            );

            self::$mainClass = self::getMainClass();

            $excludedAttribute = ReflectionUtils::getAttributeFromClass(self::$mainClass->getClassName(), Exclude::class);
            $toExclude = ($excludedAttribute?->newInstance()->getClasses()) ?? [];

            self::$declaredClasses = array_map(fn($class) =>
                $class->getName(), ClassFinder::getDeclaredClasses($dir, $toExclude)
            );

            self::$configurations = self::getConfigurations();
            self::$leafManager = LeafManager::ofConfigurations(self::$configurations);

            foreach (self::$leafManager->getLeafs() as $leaf) {
                DependencyInjectionService::addResource($leaf);
            }

            self::$controllers = self::getControllers();
            self::$restControllers = self::getRestControllers();

            self::declareRoutes();

            self::$dataSource = self::getDataSource();

            AvocadoORMSettings::fromExistingSource(self::$dataSource);

            AvocadoRouter::listen();
        } catch (Exception $e) {
            ApplicationExceptionsAdvisor::process($e);
        }
    }

    /**
     * @throws MissingAnnotationException
     */
    public static final function getMainClass(): Avocado {

        foreach (ClassFinder::getClasses() as $class) {
            $avAttr = ReflectionUtils::getAttributeFromClass($class, Avocado::class);

            if ($avAttr) {
                return new Avocado($class->getName());
            }
        }

        throw new MissingAnnotationException(sprintf("Missing %s annotation on main application class.", Avocado::class));
    }

    private static function getControllers(): array {
        $declaredClasses = self::$declaredClasses;
        $controllers = [];

        foreach ($declaredClasses as $class) {
            try {
                $reflection = new ReflectionClass($class);

                if (Controller::isController($reflection)) {
                    $controllers[] = new Controller($class, $reflection);
                }
            } catch (ReflectionException) {
                continue;
            }
        }

        return $controllers;
    }

    private static function getRestControllers(): array {
        $controllers = [];

        foreach (self::$controllers as $controller) {
            $reflection = $controller->getTargetReflection();

            if (Controller::isRestController($reflection)) {
                $controllers[] = $controller;
            }
        }

        return $controllers;
    }

    /**
     * @return Configuration[]
     * */
    private static function getConfigurations(): array {
        $configurations = [];

        foreach (self::$declaredClasses as $class) {
            if (Configuration::isConfiguration($class)) {
                try {
                    $configurations[] = new Configuration($class);
                } catch (ClassNotFoundException) {}
            }
        }

        return $configurations;
    }

    private static function declareRoute(MethodMapping $mapping): void {
        $endpoint = $mapping->getEndpoint();
        $callback = $mapping->getCallback();

        switch ($mapping->getHTTPMethod()) {
            case HTTPMethod::GET:
                AvocadoRouter::GET($endpoint, [], $callback);
                break;
            case HTTPMethod::POST:
                AvocadoRouter::POST($endpoint, [], $callback);
                break;
            case HTTPMethod::DELETE:
                AvocadoRouter::DELETE($endpoint, [], $callback);
                break;
            case HTTPMethod::PUT:
                AvocadoRouter::PUT($endpoint, [], $callback);
                break;
            case HTTPMethod::PATCH:
                AvocadoRouter::PATCH($endpoint, [], $callback);
                break;
        }
    }

    private static function declareRoutes(): void {
        foreach (self::$restControllers as $controller) {
            foreach ($controller->getMappings() as $mapping) {
                if($mapping) {
                    self::declareRoute($mapping);
                }
            }
        }
    }

    private static function getDataSource(): DataSource {
        return self::$leafManager->getLeafByClass(DataSource::class);
    }

    public static function getLeafManager(): LeafManager {
        return self::$leafManager;
    }
}
