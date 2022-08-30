<?php

namespace Avocado\AvocadoApplication\DependencyInjection;

interface Resourceable {
    public function getTargetResourceClass();
    public function getTargetInstance();
    public function getAlternativeName(): string;
}
