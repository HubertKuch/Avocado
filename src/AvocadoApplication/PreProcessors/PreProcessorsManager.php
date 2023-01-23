<?php

namespace Avocado\AvocadoApplication\PreProcessors;

use Avocado\Application\Application;
use Avocado\AvocadoApplication\Controller\ParameterProviders\SpecificParametersPreProcessor;
use Avocado\AvocadoApplication\Interceptors\WebRequestAnnotationInterceptorAdapter;
use Avocado\Utils\ClassFinder;

class PreProcessorsManager {

    /**
     * @return SpecificParametersPreProcessor[]
     * */
    public static function getControllerPreProcessors(): array {
        $processors = Application::preProcessors();

        return array_filter(
            $processors,
            fn($obj) => ClassFinder::getClassReflectionByName($obj::class)->implementsInterface(SpecificParametersPreProcessor::class)
        );
    }

    /**
     * @return WebRequestAnnotationInterceptorAdapter[]
     * */
    public static function getPreHandlerPreprocessors(): array {
        $processors = Application::preProcessors();

        return array_filter($processors, fn($processor) => ClassFinder::getClassReflectionByName($processor::class)->implementsInterface(WebRequestAnnotationInterceptorAdapter::class));
    }


    public static function getPreProcessors(): array {
        return Application::preProcessors();
    }

}