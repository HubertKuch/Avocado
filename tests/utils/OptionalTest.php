<?php

namespace Avocado\Utils;

use Exception;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preventGlobalState disabled
 * */
class OptionalTest extends TestCase {

    public function testEmpty() {
        $opt = Optional::empty();

        self::assertNull($opt->get());
    }

    public function testOrElseGet() {
        $opt = new Optional(null);
        $value = $opt->orElseGet(12);

        self::assertSame(12, $value);
    }

    public function testOf() {
        $opt = Optional::of([24, 12]);
        $val = $opt->get();

        self::assertSame([24, 12], $val);
    }

    public function testOrElseDo() {
        $opt = Optional::of(null);

        $opt->orElseDo(fn() => print "test");

        self::assertSame("test", ob_get_contents());
    }

    public function testGet() {
        $opt = Optional::of(234);

        self::assertSame(234, $opt->get());
    }

    public function testIsPresent() {
        $opt = Optional::of([]);

        self::assertTrue($opt->isPresent());
    }

    public function testIsEmpty() {
        $opt = Optional::of([]);

        self::assertFalse($opt->isEmpty());
    }

    public function testOrElseThrow() {
        $this->expectException(Exception::class);

        (Optional::empty())->orElseThrow(fn() => throw new Exception("Exception test"));
    }
}
