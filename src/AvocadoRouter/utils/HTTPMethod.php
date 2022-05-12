<?php

namespace Avocado\AvocadoRouter\utils;

enum HTTPMethod: string {
    case GET = "GET";
    case POST = "POST";
    case PUT = "PUT";
    case PATCH = "PATCH";
    case DELETE = "DELETE";
    case HEAD = "HEAD";
    case INFO = "INFO";
    case OPTIONS = "OPTIONS";
    case TRACE = "TRACE";
    case CONNECT = "CONNECT";
}
