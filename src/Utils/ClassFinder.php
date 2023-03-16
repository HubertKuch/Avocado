<?php

namespace Avocado\Utils;

use Nette\Loaders\RobotLoader;
use ReflectionClass;
use ReflectionException;
use Throwable;

class ClassFinder {
    private static array $classes = [];
    private static array $reflections = [];

    public static function getDeclaredClasses(string $dir, array $toExclude = []): array {
        $loader = new RobotLoader();

        $loader->addDirectory($dir);
        $loader->setTempDirectory(sys_get_temp_dir());
        $loader->excludeDirectory($dir . "/vendor");
        $loader->register();

        if (file_exists($dir . "/vendor/hubert/")) {
            $loader->addDirectory($dir . "/vendor/hubert");
        }

        $loader->refresh();

        self::$classes = $loader->getIndexedClasses();
        $files = is_string(key(self::$classes)) ? array_values(self::$classes) : [];

        if (is_string(key(self::$classes))) {
            self::$classes = array_keys(self::$classes);
        }

        if (!empty($toExclude)) {
            self::$classes = self::excludeClasses($toExclude);
        }

        if (($_ENV['AVOCADO_ENVIRONMENT'] ?? "DEVELOPMENT") == "PRODUCTION") {
            self::$classes = self::excludeNamespace("AvocadoApplication\\Tests\\");
            self::$classes = self::excludeNamespace("Avocado\\Tests\\");
        }

        foreach ($files as $path) {
            try {
                require_once $path;
            } catch (Throwable $e) {
                continue;
            }
        }

        return self::$classes;
    }

    private static function excludeClasses(array $classes): array {
        return array_filter(self::$classes, fn($class) => !in_array($class, $classes));
    }

    public static function getClasses(): array {
        return array_unique(self::$classes);
    }

    public static function getClassReflectionByName(string $className): ?ReflectionClass {
        $ref = Arrays::find(self::$reflections, fn($reflection) => $reflection->getName() === $className);

        if (!$ref) {
            try {
                $ref = new ReflectionClass($className);
                self::$reflections[] = $ref;
            } catch (ReflectionException $e) {
                return null;
            }
        }

        return $ref;
    }

    private static function excludeNamespace(string $namespace): array {
        return array_filter(self::$classes, function ($class) use ($namespace) {
            return !str_starts_with($class, $namespace);
        });
    }
}
