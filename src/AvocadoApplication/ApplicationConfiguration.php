<?php

namespace Avocado\Application;

use Avocado\AvocadoApplication\Attributes\Configuration;
use Avocado\AvocadoApplication\Exceptions\MissingKeyException;
use Avocado\Utils\Arrays;
use Avocado\Utils\StandardObjectMapper;
use ReflectionException;

class ApplicationConfiguration {

    /**
     * @var $configurations object[]
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
     * @return object[]
     * */
    public function getConfigurations(): array {
        return $this->configurations;
    }

    /**
     * @param string $class Class string fqn
     * @return object|null Returns a instance of configuration class
     */
    public function getConfiguration(string $class): ?object {
        return Arrays::find(self::getConfigurations(), fn($conf) => $conf::class === $class);
    }
}