<?php

namespace Avocado\Application;

use Avocado\AvocadoApplication\ApplicationExceptionsAdvisor;
use Avocado\AvocadoApplication\Attributes\Avocado;
use Avocado\AvocadoApplication\Attributes\Configuration;
use Avocado\AvocadoApplication\Attributes\Exclude;
use Avocado\AvocadoApplication\Exceptions\ClassNotFoundException;
use Avocado\AvocadoApplication\Exceptions\MissingAnnotationException;
use Avocado\AvocadoApplication\Leafs\LeafManager;
use Avocado\AvocadoApplication\PreProcessors\PreProcessor;
use Avocado\AvocadoApplication\ResponseConsuming\MainHttpConsumer;
use Avocado\DataSource\DataSource;
use Avocado\HTTP\HTTPMethod;
use Avocado\HTTP\Managers\HttpConsumer;
use Avocado\ORM\AvocadoORMSettings;
use Avocado\Router\AvocadoRouter;
use Avocado\Utils\ClassFinder;
use Avocado\Utils\ReflectionUtils;
use AvocadoApplication\Attributes\Resource;
use AvocadoApplication\DependencyInjection\DependencyInjectionService;
use AvocadoApplication\Mappings\MethodMapping;
use Exception;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Yaml\Yaml;

final class Application {
    private static array $declaredClasses = [];
    /** @var $configurations Configuration[] */
    private static array $configurations = [];
    private static array $controllers = [];
    private static array $restControllers = [];
    private static array $preProcessors = [];
    private static LeafManager $leafManager;
    private static DataSource $dataSource;
    private static Avocado $mainClass;
    private static HttpConsumer $httpConsumer;
    private static ApplicationConfiguration $configuration;

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

            self::$preProcessors = self::getPreProcessors();
            self::$configurations = self::getConfigurations();
            self::$leafManager = LeafManager::ofConfigurations(self::$configurations);

            foreach (self::$leafManager->getLeafs() as $leaf) {
                DependencyInjectionService::addResource($leaf);
            }

            DependencyInjectionService::init();

            self::$httpConsumer = DependencyInjectionService::getResourceByType(MainHttpConsumer::class)->getTargetInstance();

            self::$controllers = self::getControllers();
            self::$restControllers = self::getRestControllers();

            self::declareRoutes();

            self::$dataSource = self::getDataSource();

            self::$configuration = self::initConfiguration();

            AvocadoORMSettings::fromExistingSource(self::$dataSource);
            AvocadoRouter::listen();
            $data = AvocadoRouter::invokeMatchedRoute();

            if ($data && $data->getData()) {
                self::$httpConsumer -> consume($data->getData(), $data->getStatus());
            }

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
                AvocadoRouter::GET($endpoint, [], $callback, ["req", "res"]);
                break;
            case HTTPMethod::POST:
                AvocadoRouter::POST($endpoint, [], $callback, ["req", "res"]);
                break;
            case HTTPMethod::DELETE:
                AvocadoRouter::DELETE($endpoint, [], $callback, ["req", "res"]);
                break;
            case HTTPMethod::PUT:
                AvocadoRouter::PUT($endpoint, [], $callback, ["req", "res"]);
                break;
            case HTTPMethod::PATCH:
                AvocadoRouter::PATCH($endpoint, [], $callback, ["req", "res"]);
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

    private static function getPreProcessors(): array {
        $validClasses = array_filter(self::$declaredClasses, fn($className) => PreProcessor::isAnnotated(ClassFinder::getClassReflectionByName($className)));

        return array_map(function($class) {
            $ref = ClassFinder::getClassReflectionByName($class);
            $instance = $ref -> newInstanceWithoutConstructor();

            DependencyInjectionService::addResource(new Resource($class, ReflectionUtils::getAllTypes($instance), $class, $instance));

            return $instance;
        }, $validClasses);
    }

    /**
     * @return array
     */
    public static function preProcessors(): array {
        return self::$preProcessors;
    }

    public static function getProjectDirectory(): string {
        return dirname(ClassFinder::getClassReflectionByName(self::$mainClass->getClassName())->getFileName());
    }

    private static function getPropertiesConfigurations(): array {
        $allConfigurations = self::$configurations;

        return array_filter($allConfigurations, fn($configuration) => $configuration->isConfigurationsProperties());
    }

    private static function initConfiguration(): ApplicationConfiguration {
        $mainDir = self::getProjectDirectory();
        $CONFIGURATION_FILE_BASE = "application";
        $ALLOWED_EXTENSIONS = ["json", "yaml"];

        if (!is_dir($mainDir)) {
            return new ApplicationConfiguration();
        }

        foreach ($ALLOWED_EXTENSIONS as $EXTENSION) {
            $baseFilePath = $mainDir.DIRECTORY_SEPARATOR.$CONFIGURATION_FILE_BASE;
            $baseFullPath =  $baseFilePath. "." .$EXTENSION;

            $declaredConfigurationsPropertiesClasses = self::getPropertiesConfigurations();

            if (file_exists($baseFullPath)) {
                $properties = self::getPropertiesFileAsArray($baseFullPath, $EXTENSION);

                return ApplicationConfiguration::from($declaredConfigurationsPropertiesClasses, $properties);
            }
        }

        return new ApplicationConfiguration();
    }

    public static function getConfiguration(): ApplicationConfiguration {
        return self::$configuration;
    }

    private static function getPropertiesFileAsArray(string $baseFullPath, string $EXTENSION) {
        $content = file_get_contents($baseFullPath);

        if ($EXTENSION === "yaml") {
            return Yaml::parse($content);
        } else if ($EXTENSION === "json") {
            return json_decode($content, true);
        }

        return "[]";
    }
}
