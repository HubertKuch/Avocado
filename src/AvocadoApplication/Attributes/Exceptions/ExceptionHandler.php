<?php

namespace Avocado\AvocadoApplication\Attributes\Exceptions;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_METHOD)]
class ExceptionHandler {

    public function __construct(
        private readonly ?array $exceptions = [],
        private readonly ?string $classname = "",
        private readonly ?string $methodName = "",
    ) {}

    public function getExceptions(): array {
        return $this->exceptions;
    }

    public function isMatchException(string $exceptionClass): bool {
        return in_array($exceptionClass, $this->exceptions);
    }

    public function getClassname(): ?string {
        return $this->classname;
    }

    public function getMethodName(): ?string {
        return $this->methodName;
    }
}
