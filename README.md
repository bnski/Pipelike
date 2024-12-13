# Pipelike

> A simple Laravel package that extends the pipeline pattern with action-based pipes, lifecycle hooks, and conditional execution. 

This package was abstracted from a refactoring in-production project where a total rewrite was not possible, and I needed to wrangle poorly implemented Service classes and overgrown Actions without breaking everything.  There is plenty to hate, but it's been an effective wrangler.

## 🚀 Installation

You can install the package via Composer:

```bash
composer require bnski/pipelike
```

## 🎯 Key Features

- Action-based pipeline implementation
- Built-in validation and lifecycle hooks
- Conditional execution controls
- Fluent interface for configuration
- Transaction support with quiet mode
- Flexible model attribute updates
- Change tracking and metadata support

## 🛠️ Basic Usage

### Creating a Pipeline Action

Define the sequence of pipes your data will flow through:

```php
use Bnski\Pipelike\PipelineAction;

class CreateUserAction extends PipelineAction
{
    protected array $pipes = [
        CreateUserPipe::class,
        SendWelcomeEmailPipe::class,
        SendInternalNotificationsPipe::class,
    ];
}
```

### Running Your Action

The fluent way (for those who appreciate clean code):

```php
CreateUserAction::prepare()
    ->handle([
        'name'     => 'John Doe',
        'email'    => 'john@example.com',
        'password' => 'secret'
    ])
    ->thenReturn();
```

Or keep it simple:

```php
CreateUserAction::handle([
    'name'     => 'John Doe',
    'email'    => 'john@example.com',
    'password' => 'secret'
])->thenReturn();
```

### Creating Individual Pipes

A basic pipe that actually does something useful:

```php
use Bnski\Pipelike\ActionPipe;
use Bnski\Pipelike\Support\ValidatesBefore;

class CreateUserPipe extends ActionPipe 
{
    use ValidatesBefore;

    protected function action(Passable $passable): Passable
    {
        $passable->user = User::create([
            'name'     => $passable->name,
            'email'    => $passable->email,
            'password' => Hash::make($passable->password),
        ]);

        return $passable;
    }

    // Keep validations focused on what's needed for the pipe
		// Stay out of user land and business logic policy
		// Ex: 'user' => InstanceOfRule(User::class)
    // Your future self will thank you
    protected function rules(): array
    {
        return [
            'name'     => 'required',
            'email'    => 'required|email|unique:users',
            'password' => 'required'
        ];
    }
}
```

## 🎨 Advanced Features

### Single Attribute Updates

Because sometimes you just need to update one thing without the ceremony:

```php
AttributeUpdateAction::handle(
	model: $user,
	attribute: 'is_active',
	value: false,
	rules: ['is_active' => 'boolean'],
	messages: ['is_active.boolean' => 'Active status must be true/false'
	)->thenReturn();
```


Sometimes we need to mix these worlds and that is OK too.

```php
class UpdateUserStatusAction extends PipelineAttributeAction
{
    protected array $pipes = [
        AttributeUpdatePipe::class,
        NotifyUserStatusChangePipe::class,
        LogStatusChangePipe::class
    ];
}

// Usage
UpdateUserStatusAction::handle(
   model: $user,
   attribute: 'is_active',
   value: true
)->thenReturn();
```

### Conditional Execution

Control when your pipes should (or shouldn't) run:

```php
class NotifyAdminPipe extends ActionPipe
{
    protected function when(Passable $passable): bool
    {
        return ! app()->environment('testing');
    }
}

class NotifyAdminPipe extends ActionPipe
{
    protected function unless(Passable $passable): bool
    {
        return app()->environment('production');
    }
}
```

### Transaction Support

Optionally, you can wrap your pipe.  Be cautious here, condoms break.

```php
Pipeline::handle($passable)
    ->through([UpdateUserAction::class])
    ->quietly()  // Magic happens in a transaction
    ->thenReturn();
```

### The Passable Object

A flexible container for your data with some hacky tricks:

```php
$passable = new Passable(['status' => 'active']);

// Track changes like it's your job
$hasChanged = $passable->isDirty('status');
$unchanged  = $passable->isClean('status');

// Add metadata for extra context
$passable->havingModel($user)
    ->havingAttribute('status', 'inactive')
    ->havingRules(['status' => 'in:active,inactive']);
```

### Error Handling

Bad things happen to good people.

```php
Pipeline::handle($passable)
    ->through([UpdateUserAction::class])
    ->catch(function (Throwable $e) {
        // Take some action, or don't.
    })
    ->thenReturn();
```

## 🧪 Testing

This package uses Pest PHP for testing.

```bash
./vendor/bin/pest
```

## 📜 License

This package is open-sourced software licensed under the MIT license.

## ✨ Credits

Created by David Bednarski
