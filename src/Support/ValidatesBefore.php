<?php

namespace Bnski\Pipelike\Support;

use Bnski\Pipelike\Passable;
use Illuminate\Support\Facades\Validator;

trait ValidatesBefore
{
    protected function before(Passable $passable): Passable
    {
        Validator::validate($passable->toArray(), $this->rules(), $this->messages());
        return $passable;
    }

    protected function rules(): array
    {
        return [];
    }

    protected function messages(): array
    {
        return [];
    }
}