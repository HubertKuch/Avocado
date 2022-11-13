<?php

namespace Avocado\AvocadoApplication\PreProcessors;

class CannotBeProcessed {

    public static function of() {
        return new CannotBeProcessed();
    }

}