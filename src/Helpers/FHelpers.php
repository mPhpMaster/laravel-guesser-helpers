<?php
/*
 * Copyright © 2023. mPhpMaster(https://github.com/mPhpMaster) All rights reserved.
 */

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

if( !function_exists('getClass') ) {
    /**
     * Returns the name of the class of an object
     *
     * @param object|Model|string $object |string [optional] <p> The tested object. This parameter may be omitted when inside a class. </p>
     *
     * @return string|false <p> The name of the class of which <i>`object`</i> is an instance.</p>
     * <p>
     *      Returns <i>`false`</i> if <i>`object`</i> is not an <i>`object`</i>.
     *      If <i>`object`</i> is omitted when inside a class, the name of that class is returned.
     * </p>
     */
    function getClass($object): string|false
    {
        if( is_object($object) ) {
            return get_class(valueToObject($object));
        }

        return $object && is_string($object) && class_exists($object) ? $object : false;
    }
}

if( !function_exists('valueToObject') ) {
    /**
     * Cast value as Object
     *
     * @param $value
     *
     * @return object
     */
    function valueToObject($value)
    {
        return (object) $value;
    }
}

if( !function_exists('getMethodName') ) {
    /**
     * Returns method name by given Route->uses
     *
     * @param string $method
     *
     * @return string
     */
    function getMethodName(string $method): string
    {
        if( empty($method) ) return '';

        if( stripos($method, '::') !== false )
            $method = collect(explode('::', $method))->last();

        if( stripos($method, '@') !== false )
            $method = collect(explode('@', $method))->last();

        return $method;
    }
}

if( !function_exists('isModel') ) {
    /**
     * Determine if a given object is inherit Model class.
     *
     * @param object $object
     *
     * @return bool
     */
    function isModel($object): bool
    {
        try {
            $results = ($object instanceof Model) ||
                is_a($object, Model::class) ||
                is_subclass_of($object, Model::class);

            $results = $results || (
                    ($object instanceof \Model) ||
                    is_a($object, \Model::class) ||
                    is_subclass_of($object, \Model::class)
                );
        } catch(Exception $exception) {
            $results = false;
        }

        return $results ?? false;
    }
}

if( !function_exists('getRealClassName') ) {
    /**
     * Returns the real class name.
     *
     * @param string|object $class <p> The tested class. This parameter may be omitted when inside a class. </p>
     *
     * @return string|false <p> The name of the class of which <i>`class`</i> is an instance.</p>
     * <p>
     *      Returns <i>`false`</i> if <i>`class`</i> is not an <i>`class`</i>.
     *      If <i>`class`</i> is omitted when inside a class, the name of that class is returned.
     * </p>
     */
    function getRealClassName($class)
    {
        if( is_object($class) ) {
            $class = get_class($class);
        }
        throw_if(!class_exists($class), new Exception("Class `{$class}` not exists!"));

        try {
            $_class = eval("return new class extends {$class} { };");
        } catch(Exception $exception) {
            dd(
                $exception->getMessage()
            );
        }

        if( $_class && is_object($_class) ) {
            return get_parent_class($_class);
        }

        return false;
    }
}

if( !function_exists('getModelClass') ) {
    /**
     * Returns model class of query|model|string.
     *
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation|\Illuminate\Database\Eloquent\Model|string $model
     *
     * @return string|null
     */
    function getModelClass($model)
    {
        try {
            $_model = !is_string($model) ? getClass($model) : $model;
            if( !class_exists($_model) ) {
                if( !class_exists($__model = "\\App\\Models\\{$_model}") ) {
                    try {
                        $__model = getClass(app($_model));
                    } catch(\Exception $exception2) {
                        try {
                            $__model = getRealClassName($_model);
                        } catch(\Exception $exception3) {
                            $__model = null;
                        }
                    }
                }

                $_model = trim(is_string($__model) ? $__model : getClass($__model));
            }
        } catch(Exception $exception1) {

        }

        if( $_model ) {
            $_model = isModel($_model) ? $_model : null;
        }

        return $_model ?? null;
    }
}

if( !function_exists('guessPermissionName') ) {
    /**
     * Returns prefix of permissions name
     *
     * @param \Illuminate\Routing\Controller|string|null $controller  Controller or controller name, default: {@see currentController()}
     * @param string|null                                $action_name Permission name
     * @param string                                     $separator   Permission name separator
     *
     * @return string
     */
    function guessPermissionName($controller = null, $action_name = null, $separator = "."): string
    {
        $action_name ??= currentAction();

        $controller ??= request()->route()->getControllerClass();

        $controller = $controller instanceof \Illuminate\Routing\Controller ? get_class($controller) : ($controller ? trim($controller) : currentControllerClass(false));

        $controller = str_before(class_basename($controller), "Controller");

        $controller = snake_case($controller);

        $controller .= '#' . ($action_name ? snake_case($action_name) : '');

        return str_ireplace("#", $separator, trim($controller, "#"));
    }
}

if( !function_exists('guessUriKey') ) {
    /**
     * @param string        $class
     * @param callable|null $callback
     *
     * @return string
     */
    function guessUriKey(string $class, callable $callback = null): string
    {
        $class = Str::plural(Str::kebab(class_basename($class)));

        return is_callable($callback) ? value($callback, $class) : $class;
    }
}

if( !function_exists('guessNamespace') ) {
    /**
     * Returns namespace of class|object using debug_backtrace method
     *
     * @param string|null $append
     *
     * @return null|string
     */
    function guessNamespace($append = null, $backtrace_times = 1)
    {
        $caller = debug_backtrace();
        $caller = $caller[ $backtrace_times ];
        $class = null;
        try {
            if( isset($caller[ 'class' ]) ) {
                $class = (new ReflectionClass($caller[ 'class' ]))->getNamespaceName();
            }
            if( isset($caller[ 'object' ]) ) {
                $class = (new ReflectionClass(get_class($caller[ 'object' ])))->getNamespaceName();
            }
        } catch(ReflectionException $exception) {
            return null;
        }

        if( $append ) {
            $append = str_ireplace("/", "\\", $append);
        }

        if( $class ) {
            $class = str_ireplace("/", "\\", $class);
        }

        if( $class ) {
            $class = real_path("{$class}" . ($append ? "\\{$append}" : ""));
        }

        return $class;
    }
}

if( !function_exists('routeParameter') ) {
    /**
     * @param string|null $key
     * @param mixed $default
     *
     * @return string|null|mixed
     */
    function routeParameter($key = null, $default = null)
    {
        $parameters = currentRoute()->parameters;

        if( !$parameters ) {
            return $default;
        }

        return is_null($key) ? $parameters : array_get($parameters, $key, $default);
    }
}

if( !function_exists('getRealClassName') ) {
    /**
     * Returns the real class name.
     *
     * @param string|object $class <p> The tested class. This parameter may be omitted when inside a class. </p>
     *
     * @return string|false <p> The name of the class of which <i>`class`</i> is an instance.</p>
     * <p>
     *      Returns <i>`false`</i> if <i>`class`</i> is not an <i>`class`</i>.
     *      If <i>`class`</i> is omitted when inside a class, the name of that class is returned.
     * </p>
     */
    function getRealClassName($class)
    {
        if( is_object($class) ) {
            $class = get_class($class);
        }
        throw_if(!class_exists($class), new Exception("Class `{$class}` not exists!"));

        try {
            $_class = eval("return new class extends {$class} { };");
        } catch(Exception $exception) {
            dd(
                $exception->getMessage()
            );
        }

        if( $_class && is_object($_class) ) {
            return get_parent_class($_class);
        }

        return false;
    }
}
