<?php

namespace Avocado\AvocadoApplication\Cache\Internal;

enum InternalCacheKeys: string {
    case CONTROLLERS = 'av_controllers';
    case REST_CONTROLLERS = 'av_rest_controllers';
    case FILTERS = 'av_filters';
    case CONFIGURATIONS = 'av_configuration';
}