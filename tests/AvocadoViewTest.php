<?php

namespace Avocado\Tests\Unit;

use PHPUnit\Framework\TestCase;

use Avocado\AvocadoView\View;
use Avocado\AvocadoView\AvocadoViewNotFoundException;
use Avocado\AvocadoView\AvocadoViewException;

class AvocadoViewTest extends TestCase{
    public function testShouldThrowAvocadoViewNotFoundException() {
        self::expectException(AvocadoViewNotFoundException::class);

        new View("view.avocado");
    }

    public function testShouldThrowAvocadoException() {
        self::expectException(AvocadoViewException::class);

        new View(__DIR__."/testView.av");
    }

    public function testUndefinedVariable() {
        $view = new View(__DIR__."/testView.avocado");

        $parsedHTML = new \ReflectionProperty(View::class, 'parsedView');
        $parsedHTML = $parsedHTML->getValue($view);
        $excepted = "<p>UNDEFINED_VARIABLE_NAME</p>";

        self::assertSame($excepted, $parsedHTML);
    }

    public function testPassingSingleVariableInLine() {
        $view = new View(__DIR__."/testView.avocado", array(
            "test" => "john doe"
        ));

        $parsedHTML = new \ReflectionProperty(View::class, 'parsedView');
        $parsedHTML = $parsedHTML->getValue($view);
        $excepted = "<p>john doe</p>";

        self::assertSame($excepted, $parsedHTML);
    }

    public function testPassingMultipleVariablesInLine() {
        $view = new View(__DIR__."/multipleVarInLine.avocado", array(
            "test" => "john",
            "test1" => "doe"
        ));

        $parsedHTML = new \ReflectionProperty(View::class, 'parsedView');
        $parsedHTML = $parsedHTML->getValue($view);
        $excepted = "<p>john doe</p>";

        self::assertSame($excepted, $parsedHTML);
    }
}