<?php

namespace Avocado\AvocadoRouter;

enum MatchingStrategy: string {
    case SELF = "SELF";
    case URI = "URI";
}