<?php

namespace Avocado\AvocadoApplication\DependencyInjection;

/**
 * @template T
 * */
interface Resourceable {
    /** @return string[] */
    public function getTargetResourceTypes(): array;
    public function getMainType(): string;
    /**
     * @return T
     * */
    public function getTargetInstance();
    public function getAlternativeName(): string;
}
