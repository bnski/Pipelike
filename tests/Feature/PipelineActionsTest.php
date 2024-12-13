<?php

use Bnski\Pipelike\Support\PipelineActions;
use Bnski\Pipelike\Passable;
use Tests\TestCase;

uses(TestCase::class);

class TestPipelineAction
{
    use PipelineActions;
    
    protected array $pipes = [];
    
    public function setPipes(array $pipes): void
    {
        $this->pipes = $pipes;
    }
}

test('pipeline actions can run quietly', function () {
    $action = new TestPipelineAction(new Passable(['processed' => false]));
    
    $action->setPipes([
        function ($passable, $next) {
            $passable->processed = true;
            return $next($passable);
        }
    ]);
    
    $result = $action
        ->quietly()
        ->thenReturn();  // Changed from then()
        
    expect($result->processed)->toBeTrue();
});

test('pipeline actions can prepare empty passable', function () {
    $action = TestPipelineAction::prepare();
    
    expect($action->passable)->toBeInstanceOf(Passable::class);
});

test('pipeline actions can chain multiple pipes', function () {
    $action = new TestPipelineAction(new Passable(['count' => 0]));
    
    $action->through(
        function ($passable, $next) {
            $passable->count += 1;
            return $next($passable);
        },
        function ($passable, $next) {
            $passable->count += 1;
            return $next($passable);
        }
    );
    
    $result = $action->thenReturn(); 
    expect($result->count)->toBe(2);
});