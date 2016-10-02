# Events
PHP7 Event dispatcher, utilizing new type hints, and the `Ds\PriorityQueue` data type for improved performance and reduced complexity.

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

## Forgetting Events

You can remove all listeners for an event by calling the `forgot` dispatcher method.

```php
$dispatcher->forget(TestEvent::class); all listeners for TestEvent will be forgotten
$dispatcher->forget('*'); all listeners for the wildcard * will be forgotten
```

## Events

Events should extend the `Ecfectus\Events\Event` class, apart from that the events can be however you want to be.

The core event class simply provides a base to typehint the dispatcher from, you can include or add whatever functionality you want to the events.

In future we may add propagation features, or other yet to be decided features, instead of you having to change your code, we can add these to the core event class.

*Why not allow string event names?*

Well we pondered over this for a while, and we came to conclusion using the event name as the string representation of the class to be the best solution for multiple reasons.

1. Boilerplate code checking types and values for event names is reduced to a simple `class_name` call.
2. We know for sure what is being passed in and around the dispatcher, so we can type hint accordingly.
3. IDE and auto complete makes it much easier for developers than remembering string values.
4. In some cases addingin "flexibility" just adds confusion to documentation, confusion for users especially when working on projects where 1 developer prefers one style over another.

This way its very simple, you pass in an instance of your event, thats it.