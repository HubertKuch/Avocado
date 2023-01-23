<?php

namespace Avocado\AvocadoApplication\Interceptors;

use Avocado\AvocadoApplication\Interceptors\Utils\WebRequestHandler;
use Avocado\AvocadoApplication\PreProcessors\PreProcessor;
use Avocado\AvocadoApplication\PreProcessors\PreProcessorsManager;
use Avocado\Router\HttpRequest;
use Avocado\Router\HttpResponse;

#[PreProcessor]
class WebRequestInterceptorsProcessor {

    public function process(HttpRequest $request, HttpResponse $response, WebRequestHandler $handler): bool {
        $preProcessors = PreProcessorsManager::getPreHandlerPreprocessors();

        foreach ($preProcessors as $preProcessor) {
            $preProcessorResponse = $preProcessor->preHandle($request, $response, $handler);

            if ($preProcessorResponse === true) {
                continue;
            }

            return false;
        }

        return true;
    }


}