<?php

namespace Avocado\AvocadoApplication\DependencyInjection;

interface Resourceable {
    /** @return string[] */
    public function getTargetResourceTypes(): array;
    public function getMainType(): string;
    public function getTargetInstance();
    public function getAlternativeName(): string;
}
