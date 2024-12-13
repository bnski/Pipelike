<?php

use Bnski\Pipelike\Pipeline;
use Tests\TestCase;

uses(TestCase::class);

test('pipeline can catch exceptions', function () {
    $caught = false;
    
    $pipeline = new Pipeline(app());
    
    $pipeline
        ->send('test')
        ->through(function ($passable, $next) {
            throw new Exception('Test exception');
        })
        ->catch(function ($e) use (&$caught) {
            $caught = true;
        })
        ->then(function ($passable) {
            return $passable;
        });
        
    expect($caught)->toBeTrue();
});

test('pipeline processes pipes in order', function () {
    $order = [];
    
    $pipeline = new Pipeline(app());
    
    $result = $pipeline
        ->send('test')
        ->through([
            function ($passable, $next) use (&$order) {
                $order[] = 'first';
                return $next($passable);
            },
            function ($passable, $next) use (&$order) {
                $order[] = 'second';
                return $next($passable);
            }
        ])
        ->then(function ($passable) use (&$order) {
            $order[] = 'last';
            return $passable;
        });
        
    expect($order)->toBe(['first', 'second', 'last']);
});