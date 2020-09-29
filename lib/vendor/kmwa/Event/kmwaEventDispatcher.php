<?php

/**
 * Class kmwaEventDispatcher
 */
class kmwaEventDispatcher implements kmwaEventDispatcherInterface
{
    /**
     * @var kmwaListenerProviderInterface
     */
    private $listenerProvider;

    /**
     * @var array
     */
    private $listenerClasses = [];

    /**
     * kmwaEventDispatcher constructor.
     *
     * @param kmwaListenerProviderInterface $listenerProvider
     */
    public function __construct(kmwaListenerProviderInterface $listenerProvider)
    {
        $this->listenerProvider = $listenerProvider;
    }

    /**
     * @param kmwaEventInterface $event
     *
     * @return object|void
     */
    public function dispatch(kmwaEventInterface $event)
    {
        $listeners = $this->listenerProvider->getListenersForEvent($event);

        $response = new kmwaListenerResponse();

        foreach ($listeners as $listener) {
            try {
                if (count($listener) === 2) {
                    list($listenerClassName, $listenerMethodName) = $listener;
                    $listenerClass = $this->getListenerClass($listenerClassName);

                    if ($listenerClass !== false && method_exists($listenerClass, $listenerMethodName)) {
                        $value = $listenerClass->{$listenerMethodName}($event);
                        $response->addResponseFromListener(
                            sprintf('%s:%s', $listenerClassName, $listenerMethodName),
                            $value
                        );
                    }
                }
            } catch (Exception $exception) {
                waLog::log(
                    sprintf(
                        'Event %s handling error (%s:%s): %s',
                        $event->getName(),
                        $listener[0],
                        $listener[1],
                        $exception->getMessage()
                    )
                );
            }
        }

        return $response;
    }

    /**
     * @param string $className
     *
     * @return object
     */
    private function getListenerClass($className)
    {
        if (!isset($this->listenerClasses[$className])) {
            $this->listenerClasses[$className] = class_exists($className) ? new $className : false;
        }

        return $this->listenerClasses[$className];
    }
}
