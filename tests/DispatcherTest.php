<?php

namespace Ecfectus\Events\Test;

use Ecfectus\Events\Dispatcher;
use Ecfectus\Events\DispatcherInterface;
use Ecfectus\Events\Event;
use PHPUnit\Framework\TestCase;

class TestEvent extends Event{
    public $value = [];
}

class Subscriber{
    public function subscribe(DispatcherInterface $dispatcher){
        $dispatcher->listen(TestEvent::class, function(Event $e){
            $e->value[] = 1;
        });
        $dispatcher->listen(TestEvent::class, function(Event $e){
            $e->value[] = 2;
        });
    }
}

class DispatcherTest extends TestCase
{

    public function testEventIsCalled(){
        $dispatcher = new Dispatcher();

        $dispatcher->listen(TestEvent::class, function(Event $e){
            $e->value[] = 1;
        });

        $event = new TestEvent();

        $this->assertEquals([], $event->value);

        $result = $dispatcher->fire($event);

        $this->assertSame($event, $result);

        $this->assertEquals([1], $event->value);
        $this->assertEquals([1], $result->value);
    }

    public function testEventsAreCalled(){
        $dispatcher = new Dispatcher();

        $dispatcher->listen(TestEvent::class, function(Event $e){
            $e->value[] = 1;
        });
        $dispatcher->listen(TestEvent::class, function(Event $e){
            $e->value[] = 2;
        });

        $event = new TestEvent();

        $result = $dispatcher->fire($event);

        $this->assertSame($event, $result);

        $this->assertEquals([1, 2], $result->value);
    }

    public function testEventsAreCalledWithPriority(){
        $dispatcher = new Dispatcher();

        $dispatcher->listen(TestEvent::class, function(Event $e){
            $e->value[] = 1;
        }, 10);
        $dispatcher->listen(TestEvent::class, function(Event $e){
            $e->value[] = 2;
        }, 500);

        $event = new TestEvent();

        $result = $dispatcher->fire($event);

        $this->assertSame($event, $result);

        $this->assertEquals([2, 1], $result->value);
    }

    public function testWildcardEventsAreCalledInPriority(){
        $dispatcher = new Dispatcher();

        $dispatcher->listen('*', function(Event $e){
            $e->value[] = 0;
        }, -10);

        $dispatcher->listen('Ecfectus\Events\Test\*Event', function(Event $e){
            $e->value[] = 1;
        }, 10);

        $dispatcher->listen(TestEvent::class, function(Event $e){
            $e->value[] = 2;
        }, 1);

        $event = new TestEvent();

        $result = $dispatcher->fire($event);

        $this->assertSame($event, $result);

        $this->assertEquals([1, 2, 0], $result->value);
    }

    public function testSettingRouteResolver(){
        $dispatcher = new Dispatcher();

        $dispatcher->setResolver(function($callback = null){
            return function(Event $e) use ($callback){
                $e->value = $callback;
            };
        });

        $dispatcher->listen(TestEvent::class, 'SomeClass@method');

        $event = new TestEvent();

        $result = $dispatcher->fire($event);

        $this->assertSame($event, $result);

        $this->assertEquals('SomeClass@method', $result->value);
    }

    /**
     * @expectedException TypeError
     */
    public function testFailedSettingRouteResolver(){
        $dispatcher = new Dispatcher();

        $dispatcher->setResolver('string');
    }

    public function testForgettingEventQueue(){

        $dispatcher = new Dispatcher();

        $dispatcher->listen(TestEvent::class, function(Event $e){
            $e->value[] = 1;
        }, 10);

        $dispatcher->listen('*', function(Event $e){
            $e->value[] = 2;
        }, 10);

        $dispatcher->forget(TestEvent::class);

        $dispatcher->forget('*');

        $event = new TestEvent();

        $result = $dispatcher->fire($event);

        $this->assertEquals([], $result->value);
    }

    public function testAddingSubscriber(){
        $dispatcher = new Dispatcher();

        $subscriber = new Subscriber();

        $dispatcher->subscribe($subscriber);

        $event = new TestEvent();

        $result = $dispatcher->fire($event);

        $this->assertEquals([1,2], $result->value);
    }

    public function testAddingSubscriberAsString(){
        $dispatcher = new Dispatcher();

        $dispatcher->subscribe(Subscriber::class);

        $event = new TestEvent();

        $result = $dispatcher->fire($event);

        $this->assertEquals([1,2], $result->value);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAddingSubscriberWithoutSubscribeMethod(){
        $dispatcher = new Dispatcher();

        $dispatcher->subscribe(new class{});
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAddingSubscriberWhichIsntAClass(){
        $dispatcher = new Dispatcher();

        $dispatcher->subscribe('afunction');
    }
}
