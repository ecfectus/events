<?php
namespace Ecfectus\Events;

use Ds\PriorityQueue;

class Dispatcher implements DispatcherInterface{

    /**
     * @var null
     */
    protected $resolver = null;

    /**
     * @var array
     */
    protected $events = [];

    /**
     * @var array
     */
    protected $wildcardEvents = [];

    /**
     * Set a blank resolver for none callable callbacks.
     */
    public function __construct(){
        $this->resolver = function($callback = null){
            return null;
        };
    }

    /**
     * @inheritDoc
     */
    public function listen(string $event = '', $callback = null, $priority = 0) : DispatcherInterface
    {
        if(strpos($event, '*') !== false){
            $this->wildcardEvents[$event] = $this->getWildcardQueue($event);
            $this->wildcardEvents[$event]->push([$callback, $priority], $priority);
        }else{
            $this->events[$event] = $this->getQueue($event);
            $this->events[$event]->push($callback, $priority);
        }

        return $this;
    }

    /**
     * Returns the event queue if set, or an empty queue if not.
     *
     * @param string $event
     * @return PriorityQueue
     */
    private function getQueue(string $event = '') : PriorityQueue
    {
        return $this->events[$event] ?? new PriorityQueue();
    }

    /**
     * Returns the wildcard queue if set, or an empty queue if not.
     *
     * @param string $event
     * @return PriorityQueue
     */
    private function getWildcardQueue(string $event = '') : PriorityQueue
    {
        return $this->wildcardEvents[$event] ?? new PriorityQueue();
    }

    /**
     * @inheritDoc
     */
    public function fire(Event $event) : Event
    {
        $eventName = get_class($event);

        $queue = $this->getQueue($eventName)->copy();

        //handle wildcards
        foreach($this->wildcardEvents as $key => $wildcardQueues){
            if($this->matchesWildcard($key, $eventName)){
                $matchedQueue = $this->getWildcardQueue($key)->copy();
                foreach($matchedQueue as $cb){
                    $queue->push($cb[0], $cb[1]);
                }
            }
        }

        foreach($queue as $callback){
            if(!is_callable($callback)) {
                $callback = $this->resolve($callback);
            }
            if(is_callable($callback)){
                call_user_func($callback, $event);
            }
        }

        return $event;
    }

    /**
     * Determine if the pattern matches the event name and return bool.
     *
     * @param string $pattern
     * @param string $value
     * @return bool
     */
    private function matchesWildcard(string $pattern, string $value) : bool
    {
        $pattern = preg_quote($pattern, '#');
        // Asterisks are translated into zero-or-more regular expression wildcards
        // to make it convenient to check if the strings starts with the given
        // pattern such as "library/*", making any string check convenient.
        $pattern = str_replace('\*', '.*', $pattern);
        return (bool) preg_match('#^'.$pattern.'\z#u', $value);
    }

    /**
     * Allows the dispatcher to be extended and allow callbacks to be resolved by external function.
     *
     * @param null $callback
     * @return mixed
     */
    private function resolve($callback = null){
        return call_user_func($this->resolver, $callback);
    }

    /**
     * @inheritDoc
     */
    public function forget(string $event = '') : DispatcherInterface
    {
        if(strpos($event, '*') !== false){
            if(isset($this->wildcardEvents[$event])){
                unset($this->wildcardEvents[$event]);
            }
        }else{
            if(isset($this->events[$event])){
                unset($this->events[$event]);
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function subscribe($subscriber = null) : DispatcherInterface
    {
        if(is_string($subscriber) && class_exists($subscriber)){
            $subscriber = new $subscriber();
        }

        if(!is_object($subscriber)){
            throw new \InvalidArgumentException('You must provide either a class instance, or a class name to use as a subscriber.');
        }

        if(!method_exists($subscriber, 'subscribe')){
            throw new \InvalidArgumentException('A subscriber must have a subscribe method.');
        }

        $subscriber->subscribe($this);

        return $this;
    }


    /**
     * @inheritDoc
     */
    public function setResolver(callable $resolver) : DispatcherInterface
    {
        $this->resolver = $resolver;
        return $this;
    }

}