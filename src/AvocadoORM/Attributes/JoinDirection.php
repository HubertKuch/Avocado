<?php

namespace Avocado\AvocadoORM\Attributes;

enum JoinDirection: string {
    case LEFT = "LEFT";
    case RIGHT = "RIGHT";
    case INNER = "INNER";
    case CROSS = "CROSS";
}