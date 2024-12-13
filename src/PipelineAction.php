<?php

namespace Bnski\Pipelike;

use Bnski\Pipelike\Support\PipelineActions;
use Bnski\Pipelike\Passable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use function class_basename;
use function collect;
use function count;
use function is_array;

abstract class PipelineAction
{
    use PipelineActions;

    public static function handle(...$args): self
    {
        return new static(self::createPassable($args));
    }

    protected static function createPassable(array $args): Passable
    {
        // If a single model is passed, transform it
        if (count($args) === 1 && $args[0] instanceof Passable) {
            return $args[0];
        }

        // If a single array is passed, use the array directly
        if (count($args) === 1 && is_array($args[0])) {
            return new Passable($args[0]);
        }

        // If a single model is passed, transform it
        if (count($args) === 1 && $args[0] instanceof Model) {
            return self::transformSingleModel($args[0]);
        }

        // If multiple models are passed, check and transform them
        if (self::allModels($args)) {
            return self::transformMultipleModels($args);
        }

        // In all other cases, pass the arguments as is
        return new Passable($args);
    }

    protected static function transformSingleModel(Model $model): Passable
    {
        $model_name = Str::snake(class_basename($model));
        return new Passable([$model_name => $model]);
    }

    protected static function transformMultipleModels(array $models): Passable
    {
        // Ensure we are dealing with Eloquent models and returning them as-is
        $transformed = collect($models)->mapWithKeys(function ($model) {
            if ($model instanceof Model) {
                // Get the snake_case model name
                $model_name = Str::snake(class_basename($model));
                // Return the model instance directly without converting it to array
                return [$model_name => $model];
            }

            return [];
        })->all(); // Convert the collection to an array

        // Return the Passable instance with transformed models
        return new Passable($transformed);
    }

    protected static function allModels(array $args): bool
    {
        return collect($args)->every(fn($arg) => $arg instanceof Model);
    }
}