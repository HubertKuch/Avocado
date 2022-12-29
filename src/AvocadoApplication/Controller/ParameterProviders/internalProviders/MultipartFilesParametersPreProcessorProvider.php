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
            $incomingFiles = $_FILES;
            $files = [];

            foreach ($incomingFiles as $file) {
                $files[] = new MultipartFile(
                    $file['name'],
                    $file['size'],
                    $file['tmp_name'],
                    $file['error'],
                    ContentType::from($file['type']) ?? ContentType::TEXT_PLAIN
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