<?php

namespace Avocado\AvocadoApplication;

use Avocado\AvocadoApplication\Attributes\Exceptions\ResponseStatus;
use Avocado\HTTP\HTTPStatus;
use Avocado\Router\AvocadoResponse;
use Avocado\Utils\ReflectionUtils;
use Exception;

class ApplicationExceptionsAdvisor {

    public static function handle(Exception $exception): void {
        $responseStatusAttr = ReflectionUtils::getAttribute($exception, ResponseStatus::class);

        if ($responseStatusAttr == null) {
            throw $exception;
        }

        $responseStatusInstance = $responseStatusAttr->newInstance();
        $message = $exception->getMessage();

        $statusCode = $responseStatusInstance->getStatus();

        self::throwResponse($statusCode, $message);
    }

    private static function throwResponse(HTTPStatus $status, string $message): void {
        $response = new AvocadoResponse();

        $response->json([
            "status" => $status->value,
            "message" => $message,
        ])->withStatus($status);
    }
}
