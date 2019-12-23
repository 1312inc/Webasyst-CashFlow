<?php

/**
 * Class cashConfig
 */
class cashConfig extends waAppConfig
{
    const APP_ID = 'cash';

    /**
     * @var cashEntityPersist
     */
    private $persister;

    /**
     * @var cashRightConfig
     */
    private $rightConfig;

    /**
     * @var array
     */
    private $entityFactories = [];

    /**
     * @var array
     */
    private $models = [];

    /**
     * @var array
     */
    private $repositories = [];

    /**
     * @var kmwaHydratorInterface
     */
    private $hydrator;

    /**
     * @var kmwaEventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param string $type
     *
     * @return waCache
     */
    public function getCache($type = 'default')
    {
        if ($this->cache === null) {
            $this->cache = parent::getCache($type) ?: new waCache(new kmwaWaCacheAdapter(['type' => 'file']), 'status');
        }

        return $this->cache;
    }

    /**
     * @param string      $eventName
     * @param object|null $object
     * @param array       $params
     *
     * @return kmwaListenerResponseInterface
     */
    public function event($eventName, $object = null, $params = [])
    {
        return $this->getEventDispatcher()->dispatch(new cashEvent($eventName, $object, $params));
    }

    public function init()
    {
        parent::init();

        $this->models[''] = new cashModel();
//        $this->factories[''] = new statusBaseFactory();
//        $this->repositories[''] = new statusBaseRepository();

        $this->registerGlobal();
        $this->loadVendors();
    }

    /**
     * @return kmwaEventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        if ($this->eventDispatcher === null) {
            $this->eventDispatcher = new cashListenerProvider();
        }

        return $this->eventDispatcher;
    }

    /**
     * @param kmwaEventInterface $event
     *
     * @return array
     */
    public function waDispatchEvent(kmwaEventInterface $event)
    {
        return wa(static::APP_ID)->event($event->getName(), $event);
    }

    /**
     * @return kmwaHydratorInterface
     */
    public function getHydrator()
    {
        if ($this->hydrator === null) {
            $this->hydrator = new kmwaHydrator();
        }

        return $this->hydrator;
    }

    /**
     * @param $entity
     *
     * @return cashBaseFactory|cashAccountFactory|cashCategoryFactory|cashTransactionFactory
     */
    public function getEntityFactory($entity)
    {
        if (isset($this->factories[$entity])) {
            return $this->entityFactories[$entity];
        }

        $factoryClass = sprintf('%sFactory', $entity);

        if (!class_exists($factoryClass)) {
            return $this->entityFactories['']->setEntity($entity);
        }

        $this->entityFactories[$entity] = new $factoryClass();
        $this->entityFactories[$entity]->setEntity($entity);

        return $this->entityFactories[$entity];
    }

    /**
     * @param string $entity
     *
     * @throws waException
     */
    public function getModel($entity = false)
    {
        if ($entity === false) {
            return $this->models[''];
        }

        if (isset($this->models[$entity])) {
            return $this->models[$entity];
        }

        $modelClass = sprintf('%sModel', $entity);

        if (!class_exists($modelClass)) {
            throw new waException(sprintf('No model class for %s', $entity));
        }

        $this->models[$entity] = new $modelClass();

        return $this->models[$entity];
    }

    /**
     * @param string $entity
     *
     * @return cashBaseRepository|cashAccountRepository|cashCategoryRepository|cashTransactionRepository
     * @throws waException
     */
    public function getEntityRepository($entity)
    {
        if (isset($this->repositories[$entity])) {
            return $this->repositories[$entity]->resetLimitAndOffset();
        }

        $repositoryClass = sprintf('%sRepository', $entity);

        if (!class_exists($repositoryClass)) {
            throw new waException(sprintf('No repository class for %s', $entity));
        }

        $this->repositories[$entity] = new $repositoryClass();

        return $this->repositories[$entity];
    }

    /**
     * @param null $name
     *
     * @return array|mixed|null
     */
    public function getCronJob($name = null)
    {
        static $tasks;
        if (!isset($tasks)) {
            $tasks = [];
            $path = $this->getAppConfigPath('cron');
            if (file_exists($path)) {
                $tasks = include($path);
            } else {
                $tasks = [];
            }
        }

        return $name ? (isset($tasks[$name]) ? $tasks[$name] : null) : $tasks;
    }

    /**
     * @return string
     */
    public function getUtf8mb4ColumnsPath()
    {
        return wa('cash')->getConfig()->getAppConfigPath('utf8mb4');
    }

    public function explainLogs($logs)
    {
        return $logs;
    }

    /**
     * @return cashEntityPersist
     */
    public function getEntityPersister()
    {
        if ($this->persister === null) {
            $this->persister = new cashEntityPersist();
        }

        return $this->persister;
    }

    /**
     * @return cashRightConfig
     */
    public function getRightConfig()
    {
        if ($this->rightConfig === null) {
            $this->rightConfig = new cashRightConfig();
        }

        return $this->rightConfig;
    }

    private function registerGlobal()
    {
        if (!function_exists('stts')) {
            /**
             * @return cashConfig|SystemConfig|waAppConfig
             */
            function cash()
            {
                return wa(cashConfig::APP_ID)->getConfig();
            }
        }
    }

    protected function loadVendors()
    {
        $customClasses = [
            'lib/vendor/kmwa/Assert' => [
                'kmwaAssert',
                'kmwaAssertException',
            ],
            'lib/vendor/kmwa/Event' => [
                'kmwaEvent',
                'kmwaEventDispatcher',
                'kmwaEventDispatcherInterface',
                'kmwaEventInterface',
                'kmwaListenerProviderInterface',
                'kmwaListenerResponse',
                'kmwaListenerResponseInterface',
                'kmwaStoppableEventInterface',
            ],
            'lib/vendor/kmwa/Exception' => [
                'kmwaForbiddenException',
                'kmwaLogicException',
                'kmwaNotFoundException',
                'kmwaNotImplementedException',
            ],
            'lib/vendor/kmwa/Hydrator' => [
                'kmwaHydratableInterface',
                'kmwaHydrator',
                'kmwaHydratorInterface',
            ],
            'lib/vendor/kmwa/Wa/View' => [
                'kmwaWaJsonActions',
                'kmwaWaJsonController',
                'kmwaWaViewAction',
                'kmwaWaViewActions',
                'kmwaWaViewTrait',
            ],
            'lib/vendor/kmwa/Wa' => [
                'kmwaWaCacheAdapter',
                'kmwaWaListenerProviderAbstract',
            ],
        ];

        foreach ($customClasses as $path => $classes) {
            foreach ($classes as $class) {
                $file = wa()->getAppPath(sprintf('%s/%s.php', $path, $class), self::APP_ID);
                if (!class_exists($class, false) && file_exists($file)) {
                    waAutoload::getInstance()->add(
                        $class,
                        sprintf('wa-apps/%s/%s/%s.php', self::APP_ID, $path, $class)
                    );
                }
            }
        }
    }
}
