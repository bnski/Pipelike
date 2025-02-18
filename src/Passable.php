<?php

namespace Bnski\Pipelike;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Fluent;
use function data_get;

class Passable extends Fluent
{
    protected $attributes = [];
    protected array $with = [];
    protected array $having = [];
    protected readonly array $original;

    public function __construct(array|Arrayable $attributes = [])
    {
        $attributes = $attributes ?: [];
        $this->attributes = $attributes instanceof Arrayable ? $attributes->toArray() : $attributes;
        $this->original = $this->attributes;
        parent::__construct($attributes);
    }

    public function getOriginal($key, $default = null)
    {
        return data_get($this->original, $key, $default);
    }

    public function isDirty($key): bool
    {
        return $this->get($key) !== $this->getOriginal($key);
    }

    public function isClean($key): bool
    {
        return $this->get($key) === $this->getOriginal($key);
    }

    public function having($key, $default = null)
    {
        return data_get($this->having, $key, $default);
    }

    public function havingRules(array $rules): self
    {
        $this->having['rules'] = $rules;
        return $this;
    }

    public function havingMessages(array $messages): self
    {
        $this->having['messages'] = $messages;
        return $this;
    }

    public function havingModel($model): self
    {
        $this->having['model'] = $model;
        return $this;
    }

    public function havingAttribute(string $attribute, mixed $value): self
    {
        $this->having['attribute'] = $attribute;
        $this->having['value'] = $value;
        $this->$attribute = $value;
        return $this;
    }

    static public function SingleAttributeUpdate($model, string $attribute, mixed $value, mixed $rules, mixed $messages): self
    {
        return (new self)
            ->havingModel($model)
            ->havingAttribute($attribute, $value)
            ->havingRules([$attribute => $rules])
            ->havingMessages([$attribute => $messages]);
    }

    public function fill($attributes): static
    {

        if (!is_array($attributes) && !$attributes instanceof Arrayable) {
            return $this;
        }

        $array = is_array($attributes) ? $attributes : $attributes->toArray();

        foreach ($array as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;

    }

}