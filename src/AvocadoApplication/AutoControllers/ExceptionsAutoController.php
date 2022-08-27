<?php

namespace Avocado\AvocadoApplication\AutoControllers;

use Avocado\HTTP\HTTPStatus;
use Avocado\HTTP\ResponseBody;
use AvocadoApplication\Attributes\Resource;
use Avocado\AvocadoApplication\Exceptions\PageNotFoundException;
use Avocado\AvocadoApplication\Attributes\Exceptions\ExceptionHandler;

#[Resource]
#[ExceptionHandler]
class ExceptionsAutoController {

    public function __construct() {}

    #[ExceptionHandler([PageNotFoundException::class])]
    public function pageNotFound(): ResponseBody {
        return new ResponseBody([
            "message" => "Page was not found",
            "status" => HTTPStatus::NOT_FOUND->value
        ], HTTPStatus::NOT_FOUND);
    }
}
