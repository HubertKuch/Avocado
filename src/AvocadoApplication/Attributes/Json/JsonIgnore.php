<?php

namespace Avocado\AvocadoApplication\Attributes\Json;

use Attribute;
use Avocado\Utils\AnnotationUtils;
use Avocado\Utils\ReflectionUtils;
use ReflectionProperty;

#[Attribute(Attribute::TARGET_PROPERTY)]
class JsonIgnore {

    public function __construct() {}
}