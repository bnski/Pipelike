<?php

namespace Bnski\Pipelike;

use Closure;
use Laravel\SerializableClosure\SerializableClosure;
use Throwable;
use Illuminate\Pipeline\Pipeline as IlluminatePipeline;

use function collect;

class Pipeline extends IlluminatePipeline
{
    public array $catchCallbacks = [];

    public function catch($callback): static
    {
        $this->catchCallbacks[] = $callback instanceof Closure
            ? new SerializableClosure($callback)
            : $callback;

        return $this;
    }

    protected function handleException($passable, Throwable $e)
    {
        filled($this->catchCallbacks) ? $this->invokeCatchCallbacks($e) : throw ($e);
    }

    public function invokeCatchCallbacks($e)
    {
        collect($this->catchCallbacks)->each(function ($callback) use ($e) {
            $callback($e);
        });
    }

    /**
     * Get a Closure that represents a slice of the application onion.
     */
    protected function carry(): Closure
    {
        return function ($stack, $pipe) {
            return function ($passable) use ($stack, $pipe) {
                try {
                    if (is_callable($pipe)) {
                        return $pipe($passable, $stack);
                    } elseif (!is_object($pipe)) {
                        [$name, $parameters] = $this->parsePipeString($pipe);

                        $pipe = $this->container->make($name);

                        $parameters = array_merge([$passable, $stack], $parameters);
                    } else {
                        $parameters = [$passable, $stack];
                    }

                    $carry = method_exists($pipe, $this->method)
                        ? $pipe->{$this->method}(...$parameters)
                        : $pipe(...$parameters);

                    return $this->handleCarry($carry);
                } catch (Throwable $e) {
                    return $this->handleException($passable, $e);
                }
            };
        };
    }

    /**
     * Parse full pipe string to get name and parameters.
     */
    protected function parsePipeString($pipe): array
    {
        [$name, $parameters] = array_pad(explode(':', $pipe, 2), 2, []);

        if (is_string($parameters)) {
            $parameters = explode(',', $parameters);
        }

        return [$name, $parameters];
    }

    /**
     * Prepare the given destination callback.
     */
    protected function prepare(Closure $destination): Closure
    {
        return function ($passable) use ($destination) {
            try {
                return $destination($passable);
            } catch (Throwable $e) {
                return $this->handleException($passable, $e);
            }
        };
    }
}