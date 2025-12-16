<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Env;
use App\Enums\StatusCodes;
use App\Exception\InvalidRequestMethod;
use App\Utils\Response;

class Router
{
  private array $routes = [];
  private array $middlewares = [];

  private array $supportedHttpMethod = [
    'GET',
    'POST',
    'PUT',
    'PATCH',
    'DELETE'
  ];

  /**
   * @param callable|array $handler
   * @param callable[]|null $middlewares
   */
  public function add(string $method, string $route, callable|array $handler, ?array $middlewares = null): self
  {
    $method = strtoupper($method);

    if (!in_array($method, $this->supportedHttpMethod)) {
      Response::error("Method not supported", StatusCodes::METHOD_NOT_ALLOWED);
      throw new InvalidRequestMethod();
    }

    // Convert "/user/{id}" → "/user/([^/]+)"
    $regex = preg_replace('#\{([^}]+)\}#', '([^/]+)', $route);

    $this->routes[$method][] = [
      'route' => $route,
      'regex' => "#^" . $regex . "$#",
      'handler' => $handler
    ];

    $this->middlewares[$method][$route] = $middlewares;

    return $this;
  }

  public function resolve(string $uri, string $method)
  {
    $method = strtoupper($method);
    if (!isset($this->routes[$method])) {
      return Response::error("No routes for method {$method}");
    }

    if (str_contains($uri, '?')) {
      $uri = explode('?', $uri)[0];
    }

    $uri = rtrim($uri, '/') ?: '/';
    $uri = filter_var($uri, FILTER_SANITIZE_URL);

    foreach ($this->routes[$method] as $route) {
      if (preg_match($route['regex'], $uri, $matches)) {
        array_shift($matches);

        $handler = $route['handler'];

        $middlewaresResults = [];
        $routeMiddlewares = $this->middlewares[$method][$route['route']] ?? [];

        foreach ($routeMiddlewares as $md) {
          $result = call_user_func($md);

          if ($result === false) {
            return;
          }

          $middlewaresResults[] = $result;
        }

        $args = array_merge($middlewaresResults, $matches) ?? [];

        if (is_callable($handler)) {
          return call_user_func_array($handler, $args);
        }

        if (is_array($handler)) {
          [$class, $function] = $handler;

          if (!class_exists($class)) {
            return Response::error(null, StatusCodes::INTERNAL_SERVER_ERROR, "Class Not Found");
          }

          $class = new $class();

          if (!method_exists($class, $function)) {
            return Response::error(null, StatusCodes::INTERNAL_SERVER_ERROR, "Method in class not found");
          }

          return call_user_func_array([$class, $function], $args);
        }
      }
    }

    View::error((int) StatusCodes::NOT_FOUND->value);
    return;
  }
}
