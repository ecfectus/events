# Events
PHP7 Event dispatcher

## Usage

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

$result = $dispatcher->fire(new TestEvent()); $event->value will equal [2, 1]
```

## Wildcard Listeners