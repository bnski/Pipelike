<?php 

namespace Bnski\Pipelike\Support;

use Bnski\Pipelike\Passable;
use Bnski\Pipelike\Pipeline;
use Closure;
use Illuminate\Support\Facades\DB;
use Throwable;

trait PipelineActions
{
    protected string $via = 'handle';

    protected bool $quiet = false;

    protected array $pipes = [];

    protected int $attempts = 1;

    public function __construct(public mixed $passable)
    {
    }

    public function quietly(int $attempts = 1): self
    {
        $this->quiet = true;
        $this->attempts = $attempts;
        return $this;
    }

    public static function prepare(): self
    {
        return new static(new Passable());
    }

    public function through(...$pipes): self
    {
        $this->pipes = $pipes;
        return $this;
    }

    public function pipeline(): Pipeline
{
    $pipeline = (new Pipeline(app()))
        ->send($this->passable)
        ->through($this->pipes())
        ->via($this->via);

    if ($this->quiet) {
        try {
            // Change how we handle the transaction
            return DB::transaction(function () use ($pipeline) {
                return $pipeline;
            }, $this->attempts);
        } catch (Throwable $e) {
            logger()->error(class_basename(static::class), ['message' => $e->getMessage()]);
            throw $e;
        }
    }

    return $pipeline;
}

    public function then(Closure $closure)
    {
        return $this->pipeline()->then($closure);
    }

    public function catch(Closure $closure): Pipeline
    {
        return $this->pipeline()->catch($closure);
    }

    public function thenReturn()
    {
        return $this->pipeline()->then(function ($passable) {
            return $passable;
        });
    }

    public function when($value = null, ?callable $callback = null, ?callable $default = null)
    {
        return $this->pipeline()->when($value, $callback, $default);
    }

    public function unless($value = null, ?callable $callback = null, ?callable $default = null)
    {
        return $this->pipeline()->unless($value, $callback, $default);
    }

    protected function pipes(): array
    {
        return $this->pipes;
    }
}