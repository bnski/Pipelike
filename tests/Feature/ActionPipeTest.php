<?php

use Bnski\Pipelike\ActionPipe;
use Bnski\Pipelike\Passable;
use Tests\TestCase;

uses(TestCase::class);

test('action pipe runs the action and returns modified passable', function () {
    $action = new class extends ActionPipe {
        protected function action(Passable $passable): Passable
        {
            $passable->processed = true;
            return $passable;
        }
    };

    $passable = new Passable(['processed' => false]);
    $result = $action->handle($passable, fn($p) => $p);
    
    expect($result->processed)->toBeTrue();
});

test('action pipe runs lifecycle hooks in correct order', function () {
    $order = [];
    
    $action = new class extends ActionPipe {
        protected function before(Passable $passable): Passable
        {
            $GLOBALS['order'][] = 'before';
            return $passable;
        }

        protected function action(Passable $passable): Passable
        {
            $GLOBALS['order'][] = 'action';
            return $passable;
        }

        protected function after(Passable $passable): Passable
        {
            $GLOBALS['order'][] = 'after';
            return $passable;
        }
    };

    $GLOBALS['order'] = [];
    $passable = new Passable();
    $action->handle($passable, fn($p) => $p);
    
    expect($GLOBALS['order'])->toBe(['before', 'action', 'after']);
});

test('action pipe skips execution when when() returns false', function () {
    $action = new class extends ActionPipe {
        protected function when(Passable $passable): bool
        {
            return false;
        }

        protected function action(Passable $passable): Passable
        {
            $passable->set('processed', true);
            return $passable;
        }
    };

    $passable = new Passable(['processed' => false]);
    $result = $action->handle($passable, fn($p) => $p);
    
    expect($result->get('processed'))->toBeFalse();
});

test('action pipe skips execution when unless() returns true', function () {
    $action = new class extends ActionPipe {
        protected function unless(Passable $passable): bool
        {
            return true;
        }

        protected function action(Passable $passable): Passable
        {
            $passable->set('processed', true);
            return $passable;
        }
    };

    $passable = new Passable(['processed' => false]);
    $result = $action->handle($passable, fn($p) => $p);
    
    expect($result->get('processed'))->toBeFalse();
});