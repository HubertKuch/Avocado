<?php

namespace Avocado\AvocadoApplication\Controller\ParameterProviders\internalProviders;

use Avocado\AvocadoApplication\Attributes\Request\Multipart;
use Avocado\AvocadoApplication\Controller\ParameterProviders\SpecificParametersPreProcessor;
use Avocado\AvocadoApplication\Files\MultipartFile;
use Avocado\AvocadoApplication\PreProcessors\CannotBeProcessed;
use Avocado\AvocadoApplication\PreProcessors\PreProcessor;
use Avocado\HTTP\ContentType;
use Avocado\Router\AvocadoRequest;
use Avocado\Router\AvocadoResponse;
use Avocado\Utils\AnnotationUtils;
use ReflectionMethod;
use ReflectionParameter;

#[PreProcessor]
class MultipartFilesParametersPreProcessorProvider implements SpecificParametersPreProcessor {

    public function process(ReflectionMethod $methodRef, ReflectionParameter $parameterRef, AvocadoRequest $request, AvocadoResponse $response): mixed {
        if (AnnotationUtils::isAnnotated($parameterRef, Multipart::class)) {
            $name = $parameterRef->getName();
            /** @var Multipart $annotationInstance */
            $annotationInstance = AnnotationUtils::getInstance($parameterRef, Multipart::class);

            if ($annotationInstance->getName()) {
                $name = $annotationInstance->getName();
            }

            $incomingFiles = $_FILES;

            if (!$incomingFiles[$name]) {
                return null;
            }

            $files = [];
            $incomingFiles = $_FILES[$name];

            for ($fileIndex = 0; $fileIndex < count($incomingFiles['name']); $fileIndex++) {
                $files[] = new MultipartFile(
                    $incomingFiles['name'][$fileIndex],
                    $incomingFiles['size'][$fileIndex],
                    $incomingFiles['tmp_name'][$fileIndex],
                    $incomingFiles['error'][$fileIndex],
                    ContentType::from($incomingFiles['type'][$fileIndex]) ?? ContentType::TEXT_PLAIN
                );
            }

            if ($parameterRef->getType()->getName() === "array") {
                return $files;
            } else {
                return $files[key($files)];
            }
        }

        return CannotBeProcessed::of();
    }

}