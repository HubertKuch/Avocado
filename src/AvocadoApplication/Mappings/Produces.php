<?php

namespace Avocado\AvocadoApplication\Mappings;

use Attribute;
use Avocado\HTTP\ContentType;

#[Attribute(Attribute::TARGET_METHOD)]
class Produces {

    public function __construct(
        private readonly ContentType $contentType
    ){}

    public function getContentType(): ContentType {
        return $this->contentType;
    }

}