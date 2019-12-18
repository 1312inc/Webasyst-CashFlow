<?php

/**
 * Defines a dispatcher for events.
 */
interface kmwaEventDispatcherInterface
{
    /**
     * Provide all relevant listeners with an event to process.
     *
     * @param kmwaEventInterface $event
     *
     * @return kmwaListenerResponseInterface
     */
    public function dispatch(kmwaEventInterface $event);
}
