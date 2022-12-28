<?php

namespace Avocado\Tests\Unit\Application;

use Avocado\AvocadoApplication\Attributes\Configuration;
use AvocadoApplication\Attributes\Autowired;
use stdClass;

#[Configuration]
class InjectedTwoLeafsOfTheSameType {

    #[Autowired("user_repo")]
    private stdClass $test;

    #[Autowired("book_repo")]
    private stdClass $test2;

    public function getTest(): stdClass {
        return $this->test;
    }

    public function getTest2(): stdClass {
        return $this->test2;
    }
}