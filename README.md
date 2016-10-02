# Events
PHP7 Event dispatcher

## Usage

Usage is simple, use `listen` to add a callback, and `fire` to run the event callbacks.

```php

class TestEvent extends Ecfectus\Events\Event{
    public $value = [];
}

$dispatcher = new Ecfectus\Events\Dispatcher();

$dispatcher->listen(TestEvent::class, function(Event $e){
    $e->value[] = 2;
}, 500);

$dispatcher->listen(TestEvent::class, function(Event $e){
    $e->value[] = 1;
}, 1);

$result = $dispatcher->fire(new TestEvent()); $result->value will equal [2, 1]
```

## Wildcard Listeners
You can also add wildcard events, and priority for the events will be maintained.

```php

$dispatcher->listen('TestEv*', function(Event $e){
    $e->value[] = 3;
}, 100);

$dispatcher->listen('*', function(Event $e){
    $e->value[] = 4;
}, -10);

$result = $dispatcher->fire(new TestEvent()); $result->value will equal [2, 3, 1, 4]
```

## Callbacks

Callbacks can be anything `callable` by default, see the php docs here for details of whats available: http://php.net/manual/en/language.types.callable.php

In addition you can set a resolver which will be called in the cases where the callback isnt callable, usefull for using containers to create objects.

For example supplying event listeners in a laravel style could be achieved like this:

```php
$dispatcher->setResolver(function($callback = null){

    //return a function that can be invoked
    return function(Event $e) use ($callback){

        //parse the callback into something that can be used
        list($class, $method) = explode('@', $callback);
        $instance = $somecontainer->make($class);

        //return the result of the method
        return $instance->$method($event);
    };

});

$dispatcher->listen('*', 'MyClassName@handleEvent', -10);//MyClassName is created via the resolver and the result of the handleEvent method is returned.
```