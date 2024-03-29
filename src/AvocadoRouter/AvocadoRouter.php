<?php

namespace Avocado\Router;

use Avocado\Application\Application;
use Avocado\AvocadoApplication\Attributes\Exceptions\ResponseStatus;
use Avocado\AvocadoApplication\AutoConfigurations\nested\ServerRouterConfiguration;
use Avocado\AvocadoApplication\Controller\ParameterProviders\ControllerParametersProcessor;
use Avocado\AvocadoApplication\Exceptions\PageNotFoundException;
use Avocado\AvocadoApplication\Filters\RequestFilter;
use Avocado\AvocadoApplication\Interceptors\Utils\WebRequestHandler;
use Avocado\AvocadoApplication\Interceptors\WebRequestInterceptorsProcessor;
use Avocado\AvocadoApplication\Mappings\Produces;
use Avocado\AvocadoApplication\Middleware\MiddlewareProcessor;
use Avocado\AvocadoRouter\MatchingStrategy;
use Avocado\HTTP\ContentType;
use Avocado\HTTP\HTTPStatus;
use Avocado\HTTP\ResponseBody;
use Avocado\Utils\AnnotationUtils;
use AvocadoApplication\AutoConfigurations\AvocadoConfiguration;
use AvocadoApplication\DependencyInjection\DependencyInjectionService;
use PHPUnit\Exception;
use ReflectionMethod;
use Throwable;

class AvocadoRouter {
    private static array $routesStack = array();
    private static array $settingsStack = array();
    /**
     * @var RequestFilter[] $filters
     * */
    private static array $filters = [];
    private static bool $isNext = true;
    private static array $matchedRoute = array();
    private static HttpRequest $request;
    private static HttpResponse $response;

    public static function use(callable $setting): void {
        self::$settingsStack[] = $setting;
    }

    public static function useJSON(): void {
        self::$settingsStack[] = function (HttpRequest $req, HttpResponse $res) {
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

    private static function provideSettings(HttpRequest $req, HttpResponse $res): void {
        foreach (self::$settingsStack as $callback) {
            call_user_func($callback, $req, $res);
        }
    }

    private static function setRequestMethod(): void {
        $method = $_SERVER['REQUEST_METHOD'] ?? '';
        $postBodyCopy = json_decode(json_encode($_POST), true);
        $postBodyCopy = array_change_key_case($postBodyCopy);

        if (array_key_exists("_method", $postBodyCopy)) $method = $_POST['_method'];

        $_SERVER['REQUEST_METHOD'] = $method;
    }

    public static function listen(): void {
        self::setRequestMethod();

        $actPath = str_replace($_SERVER['SCRIPT_NAME'], "", self::getEndpoint());
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

            $req = new HttpRequest($params);
            $req->method = $method;

            $res = new HttpResponse();

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

        if ($iterationCount == count(self::$routesStack) && self::getEndpoint() !== "Standard input code") {
            throw new PageNotFoundException("Page was not found.");
        }
    }

    public static function invokeMatchedRoute(): ?ResponseBody {
        if (count(self::$matchedRoute) == 0) {
            return null;
        }

        foreach (self::$filters as $filter) {
            $isValid = $filter->filter(self::$request, self::$response);

            if ($isValid) {
                continue;
            }

            return null;
        }

        $route = self::$matchedRoute;

        $className = $route['CALLBACK'][0]::class;
        $methodName = $route['CALLBACK'][1];
        $ref = new ReflectionMethod("{$className}::{$methodName}");
        $httpStatusCode = self::getStatusCode($ref);
        $contentType = self::getContentType($ref);

        /** @var MiddlewareProcessor $middlewareProcessor*/
        $middlewareProcessor = DependencyInjectionService::getResourceByType(MiddlewareProcessor::class)->getTargetInstance();
        /**
         * @var WebRequestInterceptorsProcessor $webRequestInterceptor
         * */
        $webRequestInterceptor = DependencyInjectionService::getResourceByType(WebRequestInterceptorsProcessor::class)->getTargetInstance();

        $isNext = $middlewareProcessor->validRequest($ref, self::$request, self::$response);

        if (!$isNext) {
            return null;
        }

        if (!$webRequestInterceptor->process(self::$request,  self::$response, new WebRequestHandler($ref))) {
            return null;
        }

        $parameters = ControllerParametersProcessor::process($ref, self::$request, self::$response);

        return new ResponseBody(call_user_func_array($route['CALLBACK'], $parameters), $httpStatusCode, $contentType);
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
     * @param HttpRequest $req
     * @param HttpResponse $res
     * @return bool
     */
    public static function isMiddlewareThrowNext(mixed $middlewareStack, HttpRequest $req, HttpResponse $res): bool {
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

    /**
     * @return array
     */
    public static function getRoutesStack(): array {
        return self::$routesStack;
    }

    /**
     * @param array $matchedRoute
     */
    public static function setMatchedRoute(array $matchedRoute): void {
        self::$matchedRoute = $matchedRoute;
    }

    public static function getStatusCode(ReflectionMethod $ref): HTTPStatus {
        $hasDefinedStatusCode = AnnotationUtils::isAnnotated($ref, ResponseStatus::class);
        $httpStatusCode = HTTPStatus::OK;

        if($hasDefinedStatusCode) {
            $httpStatusCode = AnnotationUtils::getInstance($ref, ResponseStatus::class)->getStatus();
        }
        return $httpStatusCode;
    }

    public static function getContentType(ReflectionMethod $ref): ContentType {
        $hasDefinedContentType = AnnotationUtils::isAnnotated($ref, Produces::class);
        $contentType = ContentType::APPLICATION_JSON;

        if($hasDefinedContentType) {
            $contentType = AnnotationUtils::getInstance($ref, Produces::class)->getContentType();
        }

        return $contentType;
    }

    public static function getEndpoint(): string {
        try {
            /** @var $conf ServerRouterConfiguration  */
            $conf = Application::getConfiguration()
                ->getConfiguration(AvocadoConfiguration::class)
                ->getServerRouterConfiguration();

            $matchingStrategy = $conf->getMatchingStrategy();

            return match ($matchingStrategy) {
                MatchingStrategy::SELF => $_SERVER['PHP_SELF'],
                MatchingStrategy::URI => $_SERVER['REQUEST_URI']
            };
        } catch (Throwable) {
            return $_SERVER['PHP_SELF'];
        }
    }

    /**
     * @param RequestFilter[] $requestFilters
     * */
    public static function registerFilters(array $requestFilters): void {
        self::$filters = $requestFilters;
    }
}
