<?php

/**
 * Mapper from an event to the listeners that are applicable to that event.
 */
interface kmwaListenerProviderInterface
{
    /**
     * @param kmwaEventInterface $event
     *   An event for which to return the relevant listeners.
     *
     * @return iterable[callable]
     *   An iterable (array, iterator, or generator) of callables.  Each
     *   callable MUST be type-compatible with $event.
     */
    public function getListenersForEvent(kmwaEventInterface $event);
}
