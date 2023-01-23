<?php

namespace Avocado\Tests\Unit;

use Avocado\HTTP\HTTPStatus;
use PHPUnit\Framework\TestCase;
use Avocado\Router\HttpResponse;

class AvocadoResponseTest extends TestCase {

    public function testSettingsResponseStatusByIntegerValue() {
        $exceptedCode = 201;

        $res = new HttpResponse();
        $res -> withStatus($exceptedCode);

        self::assertSame($exceptedCode, http_response_code());
    }

    public function testSettingsResponseStatusByEnumType() {
        $excepted = HTTPStatus::UNAUTHORIZED->value;

        $res = new HttpResponse();
        $res->withStatus(HTTPStatus::UNAUTHORIZED);

        self::assertSame($excepted, http_response_code());
    }
}
