<?php

namespace Bnski\Pipelike;

use Bnski\Pipelike\Support\PipelineActions;
use Bnski\Pipelike\Passable;
use Illuminate\Database\Eloquent\Model;

abstract class PipelineAttributeAction
{
    use PipelineActions;

    public static function handle(Model $passable, string $attribute = null, mixed $value = null, string|array $rules = [], string|array $messages = []): self
    {
        return new static(Passable::SingleAttributeUpdate($passable, $attribute, $value, $rules, $messages));
    }
}