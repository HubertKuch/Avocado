<?php

namespace Avocado\Tests\Unit\Application;

use Avocado\AvocadoApplication\Attributes\Configuration;
use Avocado\AvocadoApplication\Attributes\Leaf;
use stdClass;

#[Configuration]
class MockedTwoLeafsOfTheSameType {

    #[Leaf(name: "user_repo")]
    public function getUserRepo(): stdClass {
        return new stdClass();
    }

    #[Leaf(name: "book_repo")]
    public function getBookRepo(): stdClass {
        return new stdClass();
    }
}