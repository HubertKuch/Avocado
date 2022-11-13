<?php

namespace Avocado\Router;

use Avocado\Application\Application;
use Avocado\AvocadoApplication\Controller\ParameterProviders\ControllerParametersProcessor;
use Avocado\AvocadoApplication\Exceptions\PageNotFoundException;
use Avocado\AvocadoApplication\PreProcessors\PreProcessorsManager;
use ReflectionMethod;

class AvocadoRouter {
    private static array $routesStack = array();
    private static array $middlewareStack = array();
    private static array $settingsStack = array();
    private static bool $isNext = true;
    private static array $notFoundStack = array();
    private static array $matchedRoute = array();
    private static AvocadoRequest $request;
    private static AvocadoResponse $response;

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

    private static function provideSettings(AvocadoRequest $req, AvocadoResponse $res): void {
        foreach (self::$settingsStack as $callback) {
            call_user_func($callback, $req, $res);
        }
    }

    private static function setRequestMethod(): void {
        $method = $_SERVER['REQUEST_METHOD'];
        $postBodyCopy = json_decode(json_encode($_POST), true);
        $postBodyCopy = array_change_key_case($postBodyCopy);

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

        if (str_contains($actPath, "?")) {
            $query = explode("?", $actPath);
            $actPath = $query[0];

            parse_str($query[1], $_GET);
        }

        self::listenRoutes($actPath, $method);
    }

    /**
     * @param string $actPath
     * @param string $method
     * @return void
     * @throws PageNotFoundException
     */
    public static function listenRoutes(string $actPath, string $method): void {
        $iterationCount = 0;

        foreach (self::$routesStack as $route) {
            $endpoint = $route['ROUTE']->getEndpoint();
            $middlewareStack = $route['MIDDLEWARE'];
            $params = array();

            $actPathWithoutParamsValues = self::getPathWithoutParams($endpoint, $actPath, $params);

            $req = new AvocadoRequest($params);
            $req->method = $method;

            $res = new AvocadoResponse();

            self::provideSettings($req, $res);

            $isMiddlewareThrowNext = self::isMiddlewareThrowNext($middlewareStack, $req, $res);

            if (self::$isNext && $isMiddlewareThrowNext && $actPathWithoutParamsValues === $endpoint && $method === $route['ROUTE']->getMethod()) {
                self::$matchedRoute = $route;
                self::$request = $req;
                self::$response = $res;

                break;
            }

            $iterationCount++;
        }

        if ($iterationCount == count(self::$routesStack) && $_SERVER['PHP_SELF'] !== "Standard input code") {
            throw new PageNotFoundException("Page was not found.");
        }
    }


    public static function invokeMatchedRoute(): void {
        if (count(self::$matchedRoute) == 0) {
            return;
        }

        $route = self::$matchedRoute;

        $className = $route['CALLBACK'][0]::class;
        $methodName = $route['CALLBACK'][1];

        $ref = new ReflectionMethod("{$className}::{$methodName}");
        $parameters = ControllerParametersProcessor::process($ref, self::$request, self::$response);

        call_user_func_array($route['CALLBACK'], $parameters);
    }

    private static function getPathWithoutParams(string $endpoint, string $actPath, array &$params): string {
        $explodedEndpoint = explode("/", $endpoint);
        $explodedActualPath = explode("/", $actPath);

        for ($i = 0; $i < count($explodedEndpoint); $i++) {
            if (!empty($explodedEndpoint[$i]) && @$explodedEndpoint[$i][0] === ':') {
                $ascIndex = substr($explodedEndpoint[$i], 1);

                if (!isset($params[$ascIndex])) {
                    @$params[$ascIndex] = @$explodedActualPath[$i];
                }
                @$explodedActualPath[$i] = @$explodedEndpoint[$i];
            }
        }

        return implode('/', $explodedActualPath);
    }

    /**
     * @param mixed $middlewareStack
     * @param AvocadoRequest $req
     * @param AvocadoResponse $res
     * @return bool
     */
    public static function isMiddlewareThrowNext(mixed $middlewareStack, AvocadoRequest $req, AvocadoResponse $res): bool {
        $isMiddlewareThrowNext = true;

        if (count($middlewareStack) > 0) {
            foreach ($middlewareStack as $middleware) {
                if (!is_array($middleware) || !is_callable($middleware)) {
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

        return $isMiddlewareThrowNext;
    }
}
