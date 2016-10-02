<?php
/**
 * Created by PhpStorm.
 * User: leemason
 * Date: 02/10/16
 * Time: 11:20
 */

namespace Ecfectus\Events;


/**
 * Interface DispatcherInterface
 * @package Ecfectus\Events
 */
interface DispatcherInterface
{

    /**
     * Push callbacks onto an event.
     *
     * @param string $event
     * @param null $callback
     * @param int $priority
     * @return DispatcherInterface
     */
    public function listen(string $event = '', $callback = null, $priority = 0) : DispatcherInterface;

    /**
     * Fire all event callbacks.
     *
     * @param Event $event
     * @return Event
     */
    public function fire(Event $event) : Event;

    /**
     * Remove the event callbacks from the dispatcher.
     *
     * @param string $event
     * @return DispatcherInterface
     */
    public function forget(string $event = '') : DispatcherInterface;

    /**
     * Set the resolver for none callable callbacks.
     *
     * @param callable $resolver
     * @return DispatcherInterface
     */
    public function setResolver(callable $resolver) : DispatcherInterface;

}