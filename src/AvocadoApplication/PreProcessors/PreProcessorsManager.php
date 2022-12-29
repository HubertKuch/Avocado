<?php

namespace Avocado\AvocadoApplication\PreProcessors;

use Avocado\Application\Application;
use Avocado\AvocadoApplication\Controller\ParameterProviders\SpecificParametersPreProcessor;
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

    public static function getPreProcessors() {
        return Application::preProcessors();
    }

}