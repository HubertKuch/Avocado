<?php

namespace Avocado\Application;

use Avocado\AvocadoApplication\Attributes\Configuration;
use Avocado\AvocadoApplication\Exceptions\MissingKeyException;
use Avocado\Utils\ClassFinder;
use Avocado\Utils\StandardObjectMapper;
use ReflectionException;

class ApplicationConfiguration {

    /**
     * @var $configurations Configuration[]
     * */
    private array $configurations = [];

    public function __construct(array $configurations = []) {
        $this->configurations = $configurations;
    }

    /**
     * @param Configuration[] $declaredConfigurationsPropertiesClasses
    */
    public static function from(array $declaredConfigurationsPropertiesClasses, array $configurationProperties): ApplicationConfiguration {
        $parsed = [];

        foreach ($declaredConfigurationsPropertiesClasses as $propertiesClass) {
            $prefix = $propertiesClass->getConfigurationsPropertiesInstance()->getPropertyPrefix();

            $root = $configurationProperties[$prefix] ?? null;

            if ($root) {
                $parsed[] = self::parse($root, $propertiesClass->getTargetReflection()->getName());
            }
        }

        return new ApplicationConfiguration($parsed);
    }

    /**
     * @throws MissingKeyException
     * @throws ReflectionException
     */
    private static function parse(array $properties, string $class): object {
        return StandardObjectMapper::arrayToObject($properties, $class);
    }

    /**
     * @return Configuration[]
     * */
    public function getConfigurations(): array {
        return $this->configurations;
    }
}