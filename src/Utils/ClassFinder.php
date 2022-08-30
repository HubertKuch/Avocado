<?php

namespace Avocado\Utils;

use ReflectionClass;
use Composer\Autoload\ClassLoader;
use Kcs\ClassFinder\Finder\ComposerFinder;

class ClassFinder {
    private static ComposerFinder $finder;
    private static $classes = [];

    public static function getDeclaredClasses(string $dir, array $toExclude = []): array {
        $loaders = ClassLoader::getRegisteredLoaders();

        self::$finder = new ComposerFinder($loaders[key($loaders)]);
        self::$finder->in($dir);

        $classes = [];

        foreach (self::$finder as $value) {
            $classes[] = $value;
        }

        $classes = [...$classes, ...self::getAvocadoClasses()];

        self::$classes = array_unique($classes);
        self::$classes = self::excludeClasses($toExclude);

        return self::$classes;
    }

    private static function getAvocadoClasses(): array {
        $classes = [];
        self::$finder->in(dirname(__DIR__, 1));

        foreach (self::$finder as $value)
            $classes[] = $value;

        return $classes;
    }

    private static function excludeClasses(array $classes): array {
        return array_filter(self::$classes, fn($class) => !in_array($class->getName(), $classes));
    }

    public static function getClasses(): array {
        return array_unique(self::$classes);
    }

    public static function getClassReflectionByName(string $className): ?ReflectionClass {
        $matched = array_filter(self::$classes, fn($class) => $class->getName() === $className);

        return $matched[0] ?? null;
    }
}
