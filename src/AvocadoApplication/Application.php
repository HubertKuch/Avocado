<?php

namespace Avocado\Application;

use Avocado\AvocadoApplication\Attributes\Configuration;
use Avocado\AvocadoApplication\Exceptions\ClassNotFoundException;
use Avocado\AvocadoApplication\Leafs\LeafManager;
use ReflectionClass;
use Avocado\HTTP\HTTPMethod;
use Avocado\Router\AvocadoRouter;
use AvocadoApplication\Mappings\MethodMapping;
use ReflectionException;

class Application {
    private static array $declaredClasses = [];
    /** @var $configurations Configuration[] */
    private static array $configurations = [];
    private static array $controllers = [];
    private static array $restControllers = [];
    private static LeafManager $leafManager;

    public static final function run(): void {
        self::$declaredClasses = self::getDeclaredClasses();
        self::$controllers = self::getControllers();
        self::$restControllers = self::getRestControllers();
        self::$configurations = self::getConfigurations();

        self::$leafManager = LeafManager::ofConfigurations(self::$configurations);

        self::declareRoutes();

        AvocadoRouter::listen();
    }

    private static function getDeclaredClasses(): array {
        return get_declared_classes();
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
}