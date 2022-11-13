<?php

namespace Avocado\AvocadoApplication\Controller\ParameterProviders\internalProviders;

use Avocado\AvocadoApplication\Attributes\Request\RequestStorageItem;
use Avocado\AvocadoApplication\Controller\ParameterProviders\SpecificParametersPreProcessor;
use Avocado\AvocadoApplication\PreProcessors\CannotBeProcessed;
use Avocado\AvocadoApplication\PreProcessors\PreProcessor;
use Avocado\Router\AvocadoRequest;
use Avocado\Router\AvocadoResponse;
use Avocado\Utils\AnnotationUtils;
use ReflectionMethod;
use ReflectionParameter;

#[PreProcessor]
class RequestStorageItemParameterPreProcessor implements SpecificParametersPreProcessor {

    public function process(ReflectionMethod $methodRef, ReflectionParameter $parameterRef, AvocadoRequest $request, AvocadoResponse $response): mixed {
        if (AnnotationUtils::isAnnotated($parameterRef, RequestStorageItem::class)) {
            /** @var RequestStorageItem $instance*/
            $instance = AnnotationUtils::getInstance($parameterRef, RequestStorageItem::class);
            $name = $instance->getName();
            $value = null;

            if (array_key_exists($name, $request->locals)) {
                $value = $request->locals[$name];
            }

            return $value ?? ($instance->getDefaultValue() ?? null);
        }

        return CannotBeProcessed::of();
    }
}