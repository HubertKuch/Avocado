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
        $loader->excludeDirectory($dir."/vendor");
        $loader->register();

        if (file_exists($dir . "/vendor/hubert/")) {
            $loader->addDirectory($dir . "/vendor/hubert");
        }

        $loader->refresh();

        $classes = $loader->getIndexedClasses();
        $classes = array_keys($classes);

        if (!empty($toExclude)) {
            $classes = self::excludeClasses($toExclude);
        }

        if (($_ENV['AVOCADO_ENVIRONMENT'] ?? "DEVELOPMENT") == "PRODUCTION") {
            $classes = self::excludeNamespace("AvocadoApplication\\Tests\\");
            $classes = self::excludeNamespace("Avocado\\Tests\\");
        }

        foreach ($classes as $class) {
            try {
                require_once $class;
            } catch (Throwable $e) {
                continue;
            }
        }


        self::$classes = $classes;

        return $classes;
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
