<?php

namespace Avocado\AvocadoApplication\ExceptionHandlerLogic;

use Avocado\Application\Application;
use Avocado\AvocadoApplication\AutoConfigurations\nested\EnvironmentConfiguration;
use AvocadoApplication\AutoConfigurations\AvocadoConfiguration;
use AvocadoApplication\Environment\EnvironmentType;
use Avocado\HTTP\ResponseBody;
use Avocado\AvocadoApplication\Attributes\Exceptions\ExceptionHandler;
use Throwable;

class StandardExceptionHandlerStrategy implements ExceptionHandlerStrategy {

    /**
     * @throws Throwable
     */
    public function handle(Throwable $throwable, ?ExceptionHandler $handler = null): ResponseBody {
        /** @var $environmentConfiguration EnvironmentConfiguration */
        $environmentConfiguration = Application::getConfiguration()?->getConfiguration(AvocadoConfiguration::class)->getEnvironmentConfiguration();

        if ($environmentConfiguration === null || ($environmentConfiguration->getEnvironmentDefinedEnvironment() === EnvironmentType::DEVELOPMENT && $environmentConfiguration->isThrows())) {
            throw $throwable;
        }

        return (new ExceptionResponseStatusStrategy())->handle($throwable, $handler);
    }
}
