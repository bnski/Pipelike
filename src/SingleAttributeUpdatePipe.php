<?php

namespace Bnski\Pipelike;

use Bnski\Pipelike\Passable;
use Bnski\Pipelike\Support\ValidatesBefore;

class SingleAttributeUpdatePipe extends ActionPipe
{
   use ValidatesBefore;

   protected function action(Passable $passable): Passable
   {
       $model = $passable->model;
       $attribute = $passable->attribute;
       $value = $passable->value;

       $model->update([$attribute => $value]);
       
       return $passable;
   }

   protected function rules(): array 
   {
       return $this->passable->rules ?? [];
   }

   protected function messages(): array
   {
       return $this->passable->messages ?? [];
   }
}

