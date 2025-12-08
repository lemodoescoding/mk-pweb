<?php

declare(strict_types=1);

namespace App;

use App\Exception\InvalidRequestMethod;
use App\Exception\InvalidRequestName;

class Router
{
    private array $routes;
    
    private array $suppportedHttpMethod = [
            'GET', 'POST'
        ];
    
    public function add(
        string $requestMethod,
        string $route,
        callable|array $method
    )
    {
        if(!in_array($requestMethod, $this->suppportedHttpMethod))
        {
            throw new InvalidRequestMethod();
        }
        
        $this->routes[$requestMethod][$route] = $method;
        
        return $this;
    }
    
    public function resolve(
        string $requestUri, 
        string $requestMethod
    ) {
        if(str_contains($requestUri, '?')){
            $requestUri = explode('?', $requestUri)[0];
        }
        
        $requestUri = filter_var($requestUri, FILTER_SANITIZE_URL);
        
        $action = $this->routes[$requestMethod][$requestUri] ?? null;
        
        if(!$action)
        {
            // throw new InvalidRequestName();
        }
        
        if(is_callable($action))
        {
            return call_user_func($action);
        }
        
        if(is_array($action))
        {
            [$class, $method] = $action;
            if(class_exists($class))
            {
                $class = new $class();
                if(method_exists($class, $method))
                {
                    return call_user_func_array([$class, $method], []);
                }
            }
        }
        
        var_dump($_GET);
        // throw new InvalidRequestName();
    }
}