<?php

namespace Avocado\AvocadoORM\Attributes\Relations;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ManyToOne {
    public function __construct() {}
}