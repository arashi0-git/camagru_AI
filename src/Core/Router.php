<?php

namespace App\Core;

class Router {
    protected $routes = [];

    public function get($path, $action) {
        $this->routes['GET'][$path] = $action;
    }

    public function post($path, $action) {
        $this->routes['POST'][$path] = $action;
    }

    public function dispatch($uri, $method) {
        $path = strtok($uri, '?'); // Remove query string

        if (isset($this->routes[$method][$path])) {
            $action = $this->routes[$method][$path];
            list($controllerName, $methodName) = explode('@', $action);
            
            $controllerClass = "App\\Controllers\\$controllerName";
            
            if (class_exists($controllerClass)) {
                $controller = new $controllerClass();
                if (method_exists($controller, $methodName)) {
                    $controller->$methodName();
                } else {
                    echo "Method $methodName not found in controller $controllerClass";
                }
            } else {
                echo "Controller $controllerClass not found";
            }
        } else {
            http_response_code(404);
            echo "404 Not Found";
        }
    }
}
