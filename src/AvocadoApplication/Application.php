<?php

declare(strict_types=1);

namespace Avocado\Application;

use Async\Yaml;
use Avocado\AvocadoApplication\ApplicationExceptionsAdvisor;
use Avocado\AvocadoApplication\Attributes\Avocado;
use Avocado\AvocadoApplication\Attributes\Configuration;
use Avocado\AvocadoApplication\Attributes\Exclude;
use Avocado\AvocadoApplication\Attributes\PropertiesSource;
use Avocado\AvocadoApplication\Cache\CacheProvider;
use Avocado\AvocadoApplication\Cache\FileCacheProvider;
use Avocado\AvocadoApplication\Cache\Internal\InternalCacheKeys;
use Avocado\AvocadoApplication\Exceptions\ClassNotFoundException;
use Avocado\AvocadoApplication\Exceptions\MissingAnnotationException;
use Avocado\AvocadoApplication\Filters\Filter;
use Avocado\AvocadoApplication\Filters\RequestFilter;
use Avocado\AvocadoApplication\Leafs\LeafManager;
use Avocado\AvocadoApplication\PreProcessors\PreProcessor;
use Avocado\AvocadoApplication\ResponseConsuming\MainHttpConsumer;
use Avocado\DataSource\DataSource;
use Avocado\HTTP\HTTPMethod;
use Avocado\HTTP\Managers\HttpConsumer;
use Avocado\ORM\AvocadoORMSettings;
use Avocado\Router\AvocadoRouter;
use Avocado\Utils\Arrays;
use Avocado\Utils\ClassFinder;
use Avocado\Utils\ReflectionUtils;
use AvocadoApplication\Attributes\Resource;
use AvocadoApplication\DependencyInjection\DependencyInjectionService;
use AvocadoApplication\Mappings\MethodMapping;
use Exception;
use ReflectionClass;
use ReflectionException;
use Throwable;

final class Application {
    private static array $declaredClasses = [];
    /** @var $configurations Configuration[] */
    private static array $configurations = [];
    private static array $controllers = [];
    private static array $restControllers = [];
    private static array $preProcessors = [];
    private static array $filters = [];
    private static array $requestFilters = [];
    private static LeafManager $leafManager;
    private static ?DataSource $dataSource;
    private static Avocado $mainClass;
    private static string $mainClassName;
    private static HttpConsumer $httpConsumer;
    private static ?ApplicationConfiguration $configuration;
    private static FileCacheProvider $cacheProvider;

    public static final function run(string $mainClass, string $dir): void {
        try {
            self::$mainClassName = $mainClass;

            self::loadClasses($dir);
            self::initConfigurations();
            self::initDependencyInjectionsService();

            self::initCacheProvider();

            self::initRestControllers();

            self::declareRoutes();
            self::initDataSource();
            self::initFilters();
            self::consumeHttp();

        } catch (Throwable $e) {
            ApplicationExceptionsAdvisor::process($e);
        }
    }

    /**
     * @throws ReflectionException
     */
    public static final function getMainClass(): Avocado {
        return new Avocado(self::$mainClassName, new ReflectionClass(self::$mainClassName));
    }

    private static function getControllers(): array {
        $classes = self::$declaredClasses;
        $controllersClasses = [];
        $controllers = [];

        if (self::$cacheProvider->isExists(InternalCacheKeys::CONTROLLERS)) {
            $classes = self::$cacheProvider->getItem(InternalCacheKeys::CONTROLLERS);
        }

        foreach ($classes as $class) {
            $reflection = ClassFinder::getClassReflectionByName($class);

            if (Controller::isController($reflection)) {
                $controllersClasses[] = $class;
                $controllers[] = new Controller($class, $reflection);
            }
        }

        self::$cacheProvider->saveItem(InternalCacheKeys::CONTROLLERS, $controllersClasses, true);

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
                } catch (ClassNotFoundException) {
                }
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
                if ($mapping) {
                    self::declareRoute($mapping);
                }
            }
        }
    }

    private static function getDataSource(): ?DataSource {
        try {
            return self::$leafManager->getLeafByClass(DataSource::class);
        } catch (Exception) {
            return null;
        }
    }

    public static function getLeafManager(): LeafManager {
        return self::$leafManager;
    }

    private static function getPreProcessors(): array {
        $validClasses = array_filter(self::$declaredClasses,
            fn($className) => PreProcessor::isAnnotated(new ReflectionClass($className)));

        return array_map(function ($class) {
            $ref = ClassFinder::getClassReflectionByName($class);
            $instance = $ref->newInstanceWithoutConstructor();

            DependencyInjectionService::addResource(new Resource($class,
                ReflectionUtils::getAllTypes($instance),
                $class,
                $instance));

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

    private static function initConfiguration(?string $path = null): ApplicationConfiguration {
        $mainDir = self::getProjectDirectory();

        if ($path) {
            $mainDir = $path;
        }

        $CONFIGURATION_FILE_BASE = "application";
        $ALLOWED_EXTENSIONS = ["yaml", "json"];

        if (!is_dir($mainDir)) {
            return new ApplicationConfiguration();
        }

        foreach ($ALLOWED_EXTENSIONS as $EXTENSION) {
            $baseFilePath = $mainDir . DIRECTORY_SEPARATOR . $CONFIGURATION_FILE_BASE;
            $baseFullPath = $baseFilePath . "." . $EXTENSION;

            $declaredConfigurationsPropertiesClasses = self::getPropertiesConfigurations();

            if (file_exists($baseFullPath)) {
                $properties = self::getPropertiesFileAsArray($baseFullPath, $EXTENSION);

                return ApplicationConfiguration::from($declaredConfigurationsPropertiesClasses, $properties);
            }
        }

        return ApplicationConfiguration::from($declaredConfigurationsPropertiesClasses, []);
    }

    public static function getConfiguration(): ?ApplicationConfiguration {
        return self::$configuration ?? null;
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

    /**
     * @return void
     * @throws ClassNotFoundException
     */
    public static function parsePlainConfigurations(): void {
        foreach (self::$configuration->getConfigurations() as $conf) {
            $configuration = new Configuration($conf::class, $conf);

            DependencyInjectionService::addResource($configuration);

            /** @var $conf Configuration */
            $indexOfPlainConfiguration = Arrays::indexOf(self::$configurations,
                fn($appConf) => $appConf->getMainType() === $conf::class);

            self::$configurations[$indexOfPlainConfiguration] = $configuration;
        }
    }

    /**
     * @return void
     * @throws ClassNotFoundException
     * @throws \Avocado\AvocadoApplication\Exceptions\InvalidResourceException
     */
    public static function initConfigurations(): void {
        self::$preProcessors = self::getPreProcessors();
        self::$configurations = self::getConfigurations();

        $propertiesSourceAttribute = ReflectionUtils::getAttributeFromClass(self::$mainClass->getReflectionClass(),
            PropertiesSource::class);

        if ($propertiesSourceAttribute) {
            self::$configuration = self::initConfiguration($propertiesSourceAttribute->newInstance()->getPath());
        } else {
            self::$configuration = self::initConfiguration();
        }

        self::parsePlainConfigurations();

        self::$leafManager = LeafManager::ofConfigurations(self::$configurations);
    }

    /**
     * @param string $dir
     * @return void
     * @throws MissingAnnotationException
     */
    public static function loadClasses(string $dir): void {
        self::$declaredClasses = ClassFinder::getDeclaredClasses($dir);

        self::$mainClass = self::getMainClass();

        $excludedAttribute = ReflectionUtils::getAttributeFromClass(self::$mainClass->getClassName(), Exclude::class);
        $toExclude = ($excludedAttribute?->newInstance()->getClasses()) ?? [];

        self::$declaredClasses = ClassFinder::getDeclaredClasses($dir, $toExclude, true);
    }

    /**
     * @return void
     * @throws \AvocadoApplication\DependencyInjection\Exceptions\ResourceException
     * @throws \AvocadoApplication\DependencyInjection\Exceptions\ResourceNotFoundException
     * @throws \AvocadoApplication\DependencyInjection\Exceptions\TooMuchResourceConstructorParametersException
     */
    public static function initDependencyInjectionsService(): void {
        foreach (self::$leafManager->getLeafs() as $leaf) {
            DependencyInjectionService::addResource($leaf);
        }

        DependencyInjectionService::init();
    }

    /**
     * @return void
     */
    public static function consumeHttp(): void {
        AvocadoRouter::listen();

        $data = AvocadoRouter::invokeMatchedRoute();

        if ($data && $data->getData() !== null) {
            self::$httpConsumer->consume($data);
        }
    }

    /**
     * @return void
     */
    public static function initDataSource(): void {
        self::$dataSource = self::getDataSource();

        if (self::$dataSource) {
            AvocadoORMSettings::fromExistingSource(self::$dataSource);
        }
    }

    /**
     * @return void
     */
    public static function initRestControllers(): void {
        self::$httpConsumer = DependencyInjectionService::getResourceByType(MainHttpConsumer::class)
                                                        ->getTargetInstance();

        self::$controllers = self::getControllers();
        self::$restControllers = self::getRestControllers();
    }

    /**
     * @description Returns arrays of class FQNs
     * @return string[]
     * */
    private static function getFilters(): array {
        return array_filter(self::$declaredClasses,
            fn($class) => ReflectionUtils::getAttributeFromClass($class, Filter::class) != null);
    }

    /**
     * @description Returns arrays of filters
     * @return RequestFilter[]
     * */
    private static function getRequestFilters(): array {
        $requestFilterClasses = array_filter(self::$filters,
            fn($class) => ReflectionUtils::implements($class, RequestFilter::class));

        return array_map(fn($class) => DependencyInjectionService::getResourceByType($class)->getTargetInstance(),
            $requestFilterClasses);
    }

    private static function initFilters(): void {
        self::$filters = self::getFilters();
        self::$requestFilters = self::getRequestFilters();

        AvocadoRouter::registerFilters(self::$requestFilters);
    }

    public static function getCacheProvider(): CacheProvider {
        return self::$cacheProvider;
    }

    private static function initCacheProvider(): void {
        self::$cacheProvider = DependencyInjectionService::getResourceByType(CacheProvider::class)->getTargetInstance();
    }

}
