<?php

namespace Avocado\AvocadoApplication\ExceptionHandlerLogic;

use Avocado\Application\Application;
use Avocado\AvocadoApplication\AutoConfigurations\nested\EnvironmentConfiguration;
use Avocado\HTTP\HTTPStatus;
use AvocadoApplication\AutoConfigurations\AvocadoConfiguration;
use AvocadoApplication\Environment\EnvironmentType;
use Exception;
use Avocado\HTTP\ResponseBody;
use Avocado\Utils\ReflectionUtils;
use Avocado\AvocadoApplication\Attributes\Exceptions\ResponseStatus;
use Avocado\AvocadoApplication\Attributes\Exceptions\ExceptionHandler;
use Throwable;

class ExceptionResponseStatusStrategy implements ExceptionHandlerStrategy {

    public function handle(Throwable $throwable, ?ExceptionHandler $handler = null): ResponseBody {
        $environmentConfiguration = Application::getConfiguration()->getConfiguration(AvocadoConfiguration::class)->getEnvironmentConfiguration();

        $responseStatusAttr = ReflectionUtils::getAttribute($throwable, ResponseStatus::class);
        $responseStatusInstance = $responseStatusAttr?->newInstance() ?? new ResponseStatus(HTTPStatus::INTERNAL_SERVER_ERROR);
        $message = $throwable->getMessage();

        $statusCode = $responseStatusInstance?->getStatus() ?? 500;

        $body = ["message" => $message, "status" => $statusCode->value, "path" => str_replace("Standard input code", "", $_SERVER['PHP_SELF'])];

        if ($environmentConfiguration->getEnvironmentDefinedEnvironment() === EnvironmentType::DEVELOPMENT) {
            $body['error'] = $throwable::class;
            $body['error_code'] = $throwable->getCode();
            $body['stack_trace'] = $throwable->getTraceAsString();
        }

        $body['status_code_string'] = $statusCode->name;

        if ($throwable->getPrevious()) {
            $body['cause'] = $throwable->getPrevious()->getMessage();
        }

        return new ResponseBody($body, $statusCode);
    }
}
