<?php

namespace Avocado\Application;

use Avocado\HTTP\HTTPMethod;
use Avocado\Router\AvocadoRouter;
use AvocadoApplication\Mappings\MethodMapping;
use ReflectionClass;

class Application {
    private static array $declaredClasses = [];
    private static array $controllers = [];
    private static array $restControllers = [];

    public static final function run(): void {
        self::$declaredClasses = self::getDeclaredClasses();
        self::$controllers = self::getControllers();
        self::$restControllers = self::getRestControllers();

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
            $reflection = new ReflectionClass($class);

            if (Controller::isController($reflection)) {
                $controllers[] = new Controller($class, $reflection);
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
