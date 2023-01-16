<?php

namespace Avocado\Utils;

use ReflectionClass;
use ReflectionException;
use Stagem\ClassFinder\ClassFinder;

class AvocadoClassFinderUtil {
    private static array $classes = [];
    private static array $reflections = [];

    public static function getDeclaredClasses(string $dir, array $toExclude = []): array {
        $finder = new ClassFinder();

        self::$classes = $finder->getClassesInDir($dir);
        self::$classes = [...self::$classes, ...self::getAvocadoClasses()];

        self::$classes = self::excludeClasses($toExclude);
        self::$classes = array_filter(self::$classes, fn($class) => $class);
        self::$classes = array_values(self::$classes);

        if (($_ENV['AVOCADO_ENVIRONMENT'] ?? "DEVELOPMENT") == "PRODUCTION") {
            self::$classes = self::excludeNamespace("AvocadoApplication\\Tests\\");
            self::$classes = self::excludeNamespace("Avocado\\Tests\\");
        }

        return self::$classes;
    }

    private static function getAvocadoClasses(): array {
        return (new ClassFinder())->getClassesInDir(dirname(__DIR__, 1));
    }

    private static function excludeClasses(array $classes): array {
        return array_filter(self::$classes, fn($class) => !in_array($class, $classes));
    }

    public static function getClasses(): array {
        return self::$classes;
    }

    public static function getClassReflectionByName(string $className): ?ReflectionClass {
        return Arrays::find(self::$reflections, fn($ref) => $ref->getName() === $className);
    }

    private static function excludeNamespace(string $namespace): array {
        return array_filter(self::$classes, function($class) use ($namespace) {
            return !str_starts_with($class, $namespace);
        });
    }

    public static function initReflections(): void {
        self::$reflections = array_map(fn($class) => new ReflectionClass($class), self::$classes);
    }

}
