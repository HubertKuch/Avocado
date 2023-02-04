<?php

namespace Avocado\Tests\Unit\Application;

class SecondTestObject {
    private ?int $test;

    public function __construct(?int $test = null) {
        $this->test = $test;
    }


}
