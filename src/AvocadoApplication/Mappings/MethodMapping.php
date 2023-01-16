<?php

namespace AvocadoApplication\Mappings;

use Avocado\Utils\AvocadoClassFinderUtil;
use Exception;
use ReflectionMethod;
use ReflectionAttribute;
use Avocado\HTTP\HTTPMethod;

class MethodMapping {
    private string $endpoint;
    private HTTPMethod $HTTPMethod;
    private array $callback;

    public function __construct(string $endpoint, HTTPMethod $HTTPMethod, array $callback) {
        $this->endpoint = $endpoint;
        $this->HTTPMethod = $HTTPMethod;
        $this->callback = $callback;
    }

    /**
     * @throws Exception
     */
    public static function getEndpointFromReflectionMethod(ReflectionMethod $method): string {
        $attributes = $method->getAttributes();

        foreach ($attributes as $attr) {
            if (self::isMethodMapping($attr)) {
                return $attr->getArguments()[0];
            }
        }

        throw new Exception("Method mapping must have to declared endpoint.");
    }

    public static function getHTTPMethodFromReflectionMethod(ReflectionMethod $method): HTTPMethod {
        $attributes = $method->getAttributes();

        foreach ($attributes as $attr) {
            if (self::isMethodMapping($attr)) {
                return match ($attr->getName()) {
                    GetMapping::class       =>  HTTPMethod::GET,
                    PostMapping::class      =>  HTTPMethod::POST,
                    PutMapping::class       =>  HTTPMethod::PUT,
                    PatchMapping::class     =>  HTTPMethod::PATCH,
                    DeleteMapping::class    =>  HTTPMethod::DELETE
                };
            }
        }

        return HTTPMethod::INFO;
    }

    public static function getCallbackFromReflectionMethod(ReflectionMethod $method, object $instance): array {
        return [$instance, $method->getName()];
    }

    public static function isMethodMapping(ReflectionAttribute $attribute): bool {
        $ref = AvocadoClassFinderUtil::getClassReflectionByName($attribute->getName());

        if (!$ref->getParentClass()) {
            return false;
        }

        return $ref->getParentClass()->getName() === MethodMapping::class;
    }

    public function getEndpoint(): string {
        return $this->endpoint;
    }

    public function getHTTPMethod(): HTTPMethod {
        return $this->HTTPMethod;
    }

    public function getCallback(): array {
        return $this->callback;
    }
}
