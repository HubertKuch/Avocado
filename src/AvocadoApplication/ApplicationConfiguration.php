<?php

namespace Avocado\Application;

use Avocado\AvocadoApplication\Attributes\Configuration;
use Avocado\AvocadoApplication\AutoConfigurations\nested\EnvironmentConfiguration;
use Avocado\AvocadoApplication\Exceptions\MissingKeyException;
use Avocado\Utils\Arrays;
use Avocado\Utils\ReflectionUtils;
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
            } else {
                $instance = $propertiesClass->getTargetInstance();
                $filedInstance = ReflectionUtils::initializeWithDefaultProperties($instance);

                $parsed[] = $filedInstance;
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
     * @template T
     * @param class-string<T> $class Class name or FQN
     * @return T|null Returns a instance of configuration class
     */
    public function getConfiguration(string $class): ?object {
        return Arrays::find(self::getConfigurations(), fn($conf) => $conf::class === $class);
    }
}