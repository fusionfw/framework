<?php

namespace Fusion\Core;

/**
 * Simple Dependency Injection Container
 */
class Container
{
    private static $instance = null;
    private $bindings = [];
    private $instances = [];

    private function __construct() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Bind a class or closure to the container
     */
    public function bind(string $abstract, $concrete = null)
    {
        if ($concrete === null) {
            $concrete = $abstract;
        }

        $this->bindings[$abstract] = $concrete;
    }

    /**
     * Bind as singleton
     */
    public function singleton(string $abstract, $concrete = null)
    {
        $this->bind($abstract, $concrete);
        $this->instances[$abstract] = null;
    }

    /**
     * Resolve from container
     */
    public function make(string $abstract, array $parameters = [])
    {
        // Return existing instance if singleton
        if (isset($this->instances[$abstract]) && $this->instances[$abstract] !== null) {
            return $this->instances[$abstract];
        }

        $concrete = $this->bindings[$abstract] ?? $abstract;

        if (is_callable($concrete)) {
            $object = $concrete($this, $parameters);
        } else {
            $object = $this->build($concrete, $parameters);
        }

        // Store singleton instance
        if (isset($this->instances[$abstract])) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    /**
     * Build object with dependency injection
     */
    private function build(string $concrete, array $parameters = [])
    {
        try {
            $reflection = new \ReflectionClass($concrete);
        } catch (\ReflectionException $e) {
            throw new \Exception("Class {$concrete} not found");
        }

        if (!$reflection->isInstantiable()) {
            throw new \Exception("Class {$concrete} is not instantiable");
        }

        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return new $concrete();
        }

        $dependencies = [];
        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();

            if ($type && !$type->isBuiltin()) {
                $dependency = $type->getName();
                $dependencies[] = $this->make($dependency);
            } elseif (isset($parameters[$parameter->getName()])) {
                $dependencies[] = $parameters[$parameter->getName()];
            } elseif ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
            } else {
                throw new \Exception("Cannot resolve parameter {$parameter->getName()}");
            }
        }

        return $reflection->newInstanceArgs($dependencies);
    }

    /**
     * Check if bound
     */
    public function bound(string $abstract): bool
    {
        return isset($this->bindings[$abstract]);
    }
}
