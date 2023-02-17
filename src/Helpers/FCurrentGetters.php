<?php
/*
 * Copyright © 2023. mPhpMaster(https://github.com/mPhpMaster) All rights reserved.
 */

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

if( !function_exists('currentController') ) {
    /**
     * @return \Illuminate\Routing\Controller|null
     * @throws \Exception
     */
    function currentController()
    {
        $route = Route::current();
        if( !$route ) return null;

        if( isset($route->controller) || method_exists($route, 'getController') ) {
            return isset($route->controller) ? $route->controller : $route->getController();
        }

        $action = $route->getAction();
        if( $action && isset($action[ 'controller' ]) ) {
            $currentAction = $action[ 'controller' ];
            [ $controller, $method ] = explode('@', $currentAction);

            return $controller ? app($controller) : $controller;
        }

        return null;
    }
}

if( !function_exists('currentControllerFQN') ) {
    /**
     * @return string|null
     */
    function currentControllerFQN(): ?string
    {
        $route = Route::current();
        if( !$route ) return null;

        if( isset($route->controller) || method_exists($route, 'getController') ) {
            [ $controller, $method ] = Str::parseCallback($route->getActionName());

            return getClass($route->controller ?? $controller);
        }

        $action = $route->getAction();
        if( $action && isset($action[ 'controller' ]) ) {
            $currentAction = $action[ 'controller' ];
            [ $controller, $method ] = explode('@', $currentAction);

            return getClass($controller);
        }

        return null;
    }
}

if( !function_exists('currentControllerClass') ) {
    /**
     * @return string|null
     */
    function currentControllerClass(bool $base_name = true): ?string
    {
        $parseResult = fn($class) => ($class = $class ?: currentControllerFQN()) && $base_name ? class_basename($class) : $class;
        if( is_null($result = $route = currentRoute()) ) {
            return $parseResult($result);
        }

        if( !is_null($result = $route->controller ?? (method_exists($route, 'getControllerClass') ? $route->getControllerClass() : null)) ) {
            return $parseResult(getClass($result));
        }

        if( $action = $route->getAction() ) {
            if( isset($action[ 'controller' ]) ) {
                if( $controller = str_ireplace([ '@', '::', '->' ], '@', $action[ 'controller' ]) ) {
                    $controller = str_before($controller, '@');
                }

                $result = $controller ?: null;
            }
        }

        return $parseResult($result);
    }
}

if( !function_exists('currentRoute') ) {
    /**
     * Returns current route
     *
     * @return \Illuminate\Foundation\Application|\Illuminate\Routing\Route|mixed
     */
    function currentRoute()
    {
        $route = Route::current();

        return $route ?: app(Route::class);
    }
}

if( !function_exists('currentAction') ) {
    /**
     * Returns current route
     *
     * @return string|null
     */
    function currentAction(): ?string
    {
        try {
            $array = explode('.', currentRoute()->getName());

            return @end($array) ?: currentActionName();
        } catch(Exception $exception) {
            return currentActionName();
        }
    }
}

if( !function_exists('currentActionName') ) {
    /**
     * @param string|null|mixed $action
     *
     * @return string|null|mixed
     */
    function currentActionName($action = null)
    {
        try {
            $action = $action ?:
                Route::current()->getActionName() ?:
                    currentRoute()->getActionMethod() ?:
                        Route::currentRouteAction() ?:
                            Route::current()->getName() ?:
                                null;

            $methodName = $action ? getMethodName($action) : null;

            return $methodName ?: null;
        } catch(Exception $exception) {

        }

        return null;
    }
}

if( !function_exists('currentModelViaControllerName') ) {
    /**
     * get current route
     *
     * @param string|null $controllerName
     *
     * @return string|null
     */
    function currentModelViaControllerName($controllerName = null): ?string
    {
        try {
            if( $controller = ($controllerName ?: currentControllerClass()) ) {
                $controller = str_before_last_count($controller, 'Controller');
                $controller = getModelClass($controller);
            }

            return $controller ?: currentModel();
        } catch(Exception $exception) {
            return currentModel();
        }
    }
}

if( !function_exists('currentModel') ) {
    /**
     * Returns current model form route
     *
     * @param mixed $default
     *
     * @return mixed
     */
    function currentModel($default = null)
    {
        try {
            return array_first(currentRoute()->parameters()) ?: value($default);
        } catch(Exception $exception) {
            return value($default);
        }
    }
}

if( !function_exists('currentNamespace') ) {
    /**
     * Returns namespace of current controller
     *
     * @return null|string Namespace
     */
    function currentNamespace()
    {
        try {
            $currentController = currentController();
            if( $currentController && (
                    (is_string($currentController) && class_exists($currentController)) ||
                    is_object($currentController)
                ) ) {
                $class = get_class($currentController);
                $namespace = (new ReflectionClass($class))->getNamespaceName();
            } else {
                return null;
            }
        } catch(ReflectionException $exception) {
            return null;
        }

        return $namespace;
    }
}

if( !function_exists('currentLocale') ) {
    /**
     * return appLocale
     *
     * @param bool $full
     *
     * @return string
     */
    function currentLocale($full = false): string
    {
        if( $full )
            return (string) app()->getLocale();

        $locale = str_replace('_', '-', app()->getLocale());
        $locale = current(explode("-", $locale));

        return $locale ?: "";
    }
}