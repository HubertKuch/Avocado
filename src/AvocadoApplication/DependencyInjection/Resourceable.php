<?php

namespace Avocado\AvocadoApplication\DependencyInjection;

interface Resourceable {
    function getTargetResourceClass();
    function getTargetInstance();
}
