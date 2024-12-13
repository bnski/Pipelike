<?php

namespace Bnski\Pipelike;

use Bnski\Pipelike\Passable;
use Closure;
use function pipeline;

abstract class ActionPipe
{
    public function handle(mixed $passable, Closure $next)
    {
        if (!$this->shouldRun($passable)) {
            return $next($passable);
        }

        $pipeline = new Pipeline(app());
        return $pipeline
            ->send($passable)
            ->through([
                fn($passable, $next) => $next($this->before($passable)),
                fn($passable, $next) => $next($this->action($passable)),
                fn($passable, $next) => $next($this->after($passable)),
            ])
            ->then($next);
    }

    protected function shouldRun(Passable $passable): bool
    {
        return $this->when($passable) && !$this->unless($passable);
    }

    protected function action(Passable $passable): Passable
    {
        return $passable;
    }

    protected function when(Passable $passable): bool
    {
        return true;
    }

    protected function unless(Passable $passable): bool
    {
        return false;
    }

    protected function before(Passable $passable): Passable
    {
        return $passable;
    }

    protected function after(Passable $passable): Passable
    {
        return $passable;
    }
}