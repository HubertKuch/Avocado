<?php

namespace Avocado\Application;

use Avocado\AvocadoApplication\ApplicationExceptionsAdvisor;
use Avocado\AvocadoApplication\Attributes\Configuration;
use Avocado\AvocadoApplication\Exceptions\ClassNotFoundException;
use Avocado\AvocadoApplication\Leafs\LeafManager;
use Avocado\DataSource\DataSource;
use Avocado\HTTP\HTTPMethod;
use Avocado\ORM\AvocadoORMSettings;
use Avocado\Router\AvocadoRouter;
use AvocadoApplication\DependencyInjection\DependencyInjectionService;
use AvocadoApplication\Mappings\MethodMapping;
use Composer\Autoload\ClassLoader;
use Kcs\ClassFinder\Finder\ComposerFinder;
use Exception;
use ReflectionClass;
use ReflectionException;

class Application {
    private static array $declaredClasses = [];
    /** @var $configurations Configuration[] */
    private static array $configurations = [];
    private static array $controllers = [];
    private static array $restControllers = [];
    private static LeafManager $leafManager;
    private static DataSource $dataSource;
    private static ComposerFinder $finder;

    public static final function run(string $dir): void {
        try {
            $loaders = ClassLoader::getRegisteredLoaders();

            self::$finder = new ComposerFinder($loaders[key($loaders)]);
            self::$finder->in($dir);

            self::$declaredClasses = self::getDeclaredClasses();
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

    private static function getDeclaredClasses(): array {
        $classes = [];

        foreach (self::$finder as $value)
            $classes[] = $value->getName();

        return $classes;
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
