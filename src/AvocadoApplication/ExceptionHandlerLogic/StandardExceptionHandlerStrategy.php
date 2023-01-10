<?php

namespace Avocado\AvocadoApplication\ExceptionHandlerLogic;

use Avocado\Application\Application;
use AvocadoApplication\AutoConfigurations\AvocadoEnvironmentConfiguration;
use AvocadoApplication\Environment\EnvironmentType;
use Avocado\HTTP\ResponseBody;
use Avocado\AvocadoApplication\Attributes\Exceptions\ExceptionHandler;
use Throwable;

class StandardExceptionHandlerStrategy implements ExceptionHandlerStrategy {

    /**
     * @throws Throwable
     */
    public function handle(Throwable $throwable, ?ExceptionHandler $handler = null): ResponseBody {
        /** @var $environmentConfiguration AvocadoEnvironmentConfiguration */
        $environmentConfiguration = Application::getConfiguration()?->getConfiguration(AvocadoEnvironmentConfiguration::class);

        if ($environmentConfiguration === null || ($environmentConfiguration->getEnvironment() === EnvironmentType::DEVELOPMENT && $environmentConfiguration->isThrows())) {
            throw $throwable;
        }

        return (new ExceptionResponseStatusStrategy())->handle($throwable, $handler);
    }
}
