<?php

namespace Avocado\Router;

use Avocado\HTTP\HTTPMethod;

class AvocadoRouter {
    private static array $routesStack = array();
    private static array $middlewareStack = array();
    private static array $settingsStack = array();
    private static bool $isNext = true;
    private static array $notFoundStack = array();

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

    private static function addEndpointToStack(AvocadoRoute $route, array $middleware, callable $callback){
        self::$routesStack[] = array(
            "ROUTE" => $route,
            "CALLBACK" => $callback,
            "MIDDLEWARE" => $middleware,
        );
    }

    public static function GET(string $endpoint, array $middleware, callable $callback): void {
        self::addEndpointToStack(AvocadoRoute::createGet($endpoint), $middleware, $callback);
    }

    public static function POST(string $endpoint, array $middleware, callable $callback): void {
        self::addEndpointToStack(AvocadoRoute::createPost($endpoint), $middleware, $callback);
    }

    public static function DELETE(string $endpoint, array $middleware, callable $callback): void {
        self::addEndpointToStack(AvocadoRoute::createDelete($endpoint), $middleware, $callback);
    }

    public static function PATCH(string $endpoint, array $middleware, callable $callback): void {
        self::addEndpointToStack(AvocadoRoute::createPatch($endpoint), $middleware, $callback);
    }

    public static function PUT(string $endpoint, array $middleware, callable $callable): void {
        self::addEndpointToStack(AvocadoRoute::createPut($endpoint), $middleware, $callable);
    }

    public static function notFoundHandler(callable $callable): void {
        self::$notFoundStack = [
            "ROUTE" => new AvocadoRoute(HTTPMethod::GET, "*"),
            "CALLBACK" => $callable
        ];
    }

    private static function provideSettings(AvocadoRequest $req, AvocadoResponse $res): void {
        foreach (self::$settingsStack as $callback) {
            call_user_func($callback, $req, $res);
        }
    }

    private static function setRequestMethod(): void {
        $method = $_SERVER['REQUEST_METHOD'];
        $postBodyCopy = json_decode(json_encode($_POST), true);
        $postBodyCopy = array_change_key_case($postBodyCopy, CASE_LOWER);

        if (array_key_exists("_method", $postBodyCopy)) $method = $_POST['_method'];

        $_SERVER['REQUEST_METHOD'] = $method;
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
        $routeCount = 0;
        foreach (self::$routesStack as $route) {
            $endpoint = $route['ROUTE']->getEndpoint();
            $middlewareStack = $route['MIDDLEWARE'];
            $params = array();

            $explodedEndpoint = explode("/", $endpoint);
            $explodedActualPath = explode("/", $actPath);

            for ($i=0; $i<count($explodedEndpoint); $i++) {
                if (!empty($explodedEndpoint[$i]) && @$explodedEndpoint[$i][0] === ':') {
                    $ascIndex = substr($explodedEndpoint[$i], 1);
                    @$params[$ascIndex] = @$explodedActualPath[$i];
                    @$explodedActualPath[$i] = @$explodedEndpoint[$i];
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

            if ($routeCount == count(self::$routesStack)-1) {
                $callback = self::$notFoundStack['CALLBACK'] ?? null;

                if ($callback) $callback($req, $res);

                break;
            }

            if (self::$isNext && $isMiddlewareThrowNext && $actPathWithoutParamsValues === $endpoint && $method === $route['ROUTE']->getMethod()) {
                $route['CALLBACK']($req, $res);
                break;
            }

            $routeCount++;
        }
    }
}
