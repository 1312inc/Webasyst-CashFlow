<?php

/**
 * Class kmwaWaListenerProviderAbstract
 */
abstract class kmwaWaListenerProviderAbstract implements kmwaListenerProviderInterface
{
    const EVENT_HANDLERS_TTL = 300;

    /**
     * @return string
     */
    abstract protected function getAppName();

    /**
     * @var array
     */
    protected $handlers;

    /**
     * kmwaListenerProviderInterface constructor.
     *
     * @param waCache $cache
     * @param array   $plugins
     */
    public function __construct(waCache $cache, array $plugins = [])
    {
        if (!is_array($this->handlers)) {
            $this->getAllHandlers($plugins);
            $cache->set(
                $this->getAppName().'_event_handlers',
                $this->handlers,
                static::EVENT_HANDLERS_TTL
            );
        }
    }

    /**
     * @param kmwaEventInterface $event
     *
     * @return iterable[callable]
     */
    public function getListenersForEvent(kmwaEventInterface $event)
    {
        return isset($this->handlers[$event->getName()]) ? $this->handlers[$event->getName()] : [];
    }

    /**
     * @param string $eventConfigFile
     */
    protected function addHandlersToEvent($eventConfigFile)
    {
        if (file_exists($eventConfigFile)) {
            $appEvents = require $eventConfigFile;
            foreach ($appEvents as $eventName => $eventHandler) {
                if (!isset($this->handlers[$eventName])) {
                    $this->handlers[$eventName] = [];
                }

                if (is_array($eventHandler[0])) {
                    $this->handlers[$eventName] += $eventHandler;
                } else {
                    $this->handlers[$eventName][] = $eventHandler;
                }
            }
        }
    }

    /**
     * @param array $plugins
     */
    protected function getAllHandlers(array $plugins)
    {
        $this->addHandlersToEvent(
            wa()->getAppPath(sprintf('lib/config/%s_events.php', $this->getAppName()), $this->getAppName())
        );

        foreach ($plugins as $pluginId => $plugin) {
            $this->addHandlersToEvent(
                wa()->getAppPath(
                    sprintf('plugins/%s/lib/config/%s_events.php', $pluginId, $this->getAppName()),
                    $this->getAppName()
                )
            );
        }
    }
}
