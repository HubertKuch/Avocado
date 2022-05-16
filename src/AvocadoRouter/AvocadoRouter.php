<?php

namespace Avocado\Router;

use Avocado\AvocadoRouter\utils\HTTPMethod;

class AvocadoRouter {
    private static array $routesStack = array();
    private static array $middlewareStack = array();
    private static array $settingsStack = array();
    private static bool $isNext = true;

    public static function use(callable $setting): void {
        self::$settingsStack[] = $setting;
    }

    public static function useJSON(): void {
        self::$settingsStack[] = function (AvocadoRequest $req, AvocadoResponse $res) {
            $jsonBody = file_get_contents('php://input');
            $parsedBody = (array) json_decode($jsonBody);

            if ($parsedBody) $req->body = $parsedBody;
        };
    }

    public static function stopNext(): void {
        self::$isNext = false;
    }

    private static function addEndpointToStack(HTTPMethod|string $method, string $endpoint, array $middleware, callable $callback){
        if ($endpoint[0] === "/") $endpoint = substr($endpoint, 1);
        if (strlen($endpoint > 0) && $endpoint[-1] === "/") $endpoint = substr($endpoint, 0, -1);
        $endpoint = trim($endpoint);

        self::$routesStack[] = array(
            "ROUTE" => new AvocadoRoute(strtoupper($method instanceof HTTPMethod ? $method->value : $method), $endpoint),
            "CALLBACK" => $callback,
            "MIDDLEWARE" => $middleware
        );
    }

    public static function GET(string $endpoint, array $middleware, callable $callback): void {
        self::addEndpointToStack(HTTPMethod::GET, $endpoint, $middleware, $callback);
    }

    public static function POST(string $endpoint, array $middleware, callable $callback): void {
        self::addEndpointToStack(HTTPMethod::POST, $endpoint, $middleware, $callback);
    }

    public static function DELETE(string $endpoint, array $middleware, callable $callback): void {
        self::addEndpointToStack(HTTPMethod::DELETE, $endpoint, $middleware, $callback);
    }

    public static function PATCH(string $endpoint, array $middleware, callable $callback): void {
        self::addEndpointToStack(HTTPMethod::PATCH, $endpoint, $middleware, $callback);
    }

    public static function PUT(string $endpoint, array $middleware, callable $callable): void {
        self::addEndpointToStack(HTTPMethod::PUT, $endpoint, $middleware, $callable);
    }

    public static function ANY(string $endpoint, array $middleware, callable $callback): void {
        self::addEndpointToStack("*", $endpoint, $middleware, $callback);
    }

    private static function provideSettings(AvocadoRequest $req, AvocadoResponse $res): void {
        foreach (self::$settingsStack as $callback) {
            call_user_func($callback, $req, $res);
        }
    }

    private static function setRequestMethod(): void {

    }

    public static function listen(): void {
        self::setRequestMethod();

        $actPath = str_replace($_SERVER['SCRIPT_NAME'], "", $_SERVER['PHP_SELF']);
        $actPath = trim($actPath);
        if ($actPath && $actPath[0] === "/") $actPath = substr($actPath, 1);
        if (strlen($actPath) > 0 && $actPath[-1] === "/") $actPath = substr($actPath, 0, -1);


        $method = $_SERVER['REQUEST_METHOD'];

        if (count($_GET) > 0) {
            $actPath = explode("?", $actPath)[0];
        }

        // LISTEN ROUTES
        foreach (self::$routesStack as $route) {
            $endpoint = $route['ROUTE']->getEndpoint();
            $middlewareStack = $route['MIDDLEWARE'];
            $params = array();

            $explodedEndpoint = explode("/", $endpoint);
            $explodedActualPath = explode("/", $actPath);

            for ($i=0; $i<count($explodedEndpoint); $i++) {
                if (@$explodedEndpoint[$i][0] === ':') {
                    $ascIndex = substr($explodedEndpoint[$i], 1);
                    $params[$ascIndex] = $explodedActualPath[$i];
                    $explodedActualPath[$i] = $explodedEndpoint[$i];
                }
            }

            $actPathWithoutParamsValues = implode('/', $explodedActualPath);
            $isMiddlewareThrowNext = true;

            $req = new AvocadoRequest($params);
            $req->method = $method;
            $res = new AvocadoResponse();

            self::provideSettings($req, $res);

            if (count($middlewareStack) > 0) {
                foreach ($middlewareStack as $middleware) {
                    if (!is_callable($middleware) || !is_string($middleware)) {
                        $type = gettype($middleware);
                        throw new \TypeError("Middleware must be callable, passed $type");
                    }

                    $middlewareResponse = call_user_func($middleware, $req, $res);
                    if (!$middlewareResponse) {
                        $isMiddlewareThrowNext = false;
                        break;
                    }

                    ob_end_clean();
                }
            }

            if (self::$isNext
                && $isMiddlewareThrowNext
                && $actPathWithoutParamsValues === $endpoint
                && $method === $route['ROUTE']->getMethod()) {
                $route['CALLBACK']($req, $res);

                break;
            }
        }
    }
}
