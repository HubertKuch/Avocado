<?php

namespace AvocadoApplication\Environment;

enum EnvironmentType: string {
    case PRODUCTION = "PRODUCTION";
    case DEVELOPMENT = "DEVELOPMENT";
}