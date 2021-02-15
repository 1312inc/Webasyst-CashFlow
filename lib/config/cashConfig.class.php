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
     * @var cashContactRightsManager
     */
    private $contactRights;

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
     * @var cashLogger
     */
    private $logger;

    /**
     * @var cashUser
     */
    private $contextUser;

    public function __construct($environment, $root_path, $application = null, $locale = null)
    {
        parent::__construct($environment, $root_path, $application, $locale);

        $this->logger = new cashLogger();
    }

    /**
     * @return cashLogger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param string $type
     *
     * @return waCache
     */
    public function getCache($type = 'default')
    {
        if ($this->cache === null) {
            $this->cache = parent::getCache($type) ?: new waCache(new waFileCacheAdapter(['type' => 'file']), 'cash');
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
            $provider = new cashListenerProvider($this->getCache(), $this->getPlugins());
            $this->eventDispatcher = new kmwaEventDispatcher($provider);
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
     * @return cashBaseFactory
     */
    public function getEntityFactory($entity)
    {
        if (isset($this->entityFactories[$entity])) {
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
     * @return cashModel
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
     * @return cashBaseRepository
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
     * @return cashContactRightsManager
     */
    public function getContactRights(): cashContactRightsManager
    {
        if ($this->contactRights === null) {
            $this->contactRights = new cashContactRightsManager();
        }

        return $this->contactRights;
    }

    /**
     * @return cashUser
     * @throws waException
     */
    public function getUser(): cashUser
    {
        if ($this->contextUser === null) {
            $this->contextUser = new cashUser(wa()->getUser());
        }

        return $this->contextUser;
    }

    /**
     * The method returns a counter to show in backend header near applications' icons.
     * Three types of response are allowed.
     * @return string|int - A prime number in the form of a int or string
     * @return array - Array with keys 'count' - the value of the counter and 'url' - icon url
     * @return array - An associative array in which the key is the object key from app.php, from the header_items.
     *                 The value must be identical to the value described in one of the previous types of response.
     */
    public function onCount()
    {
        cash()->getEventDispatcher()->dispatch(new cashEventOnCount(waRequest::request('idle')));

        try {
            $url = $this->getBackendUrl(true) . $this->application . '/';
            $request = new cashApiTransactionGetBadgeCountRequest();
            $request->today = DateTimeImmutable::createFromFormat('Y-m-d', waDateTime::format('Y-m-d'));
            $response = (new cashApiTransactionGetBadgeCountHandler())->handle($request);
            if ($response->count) {
                if (wa()->whichUI(cashConfig::APP_ID) === '2.0') {
                    $url .= 'upnext';
                }

                return ['count' => $response->count, 'url' => $url];
            }
        } catch (Exception $exception) {
            cash()->getLogger()->debug(sprintf('onCount error %s', $exception->getMessage()));
        }

        return ['count' => null, 'url' => $url];
    }

    private function registerGlobal()
    {
        if (!function_exists('cash')) {
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
        $kmwaLoaderClassPath = 'lib/vendor/kmwa/Wa/kmwaWaConfigHelper.php';
        $appPath = wa()->getAppPath($kmwaLoaderClassPath, self::APP_ID);

        if (!class_exists('kmwaWaConfigHelper', false) && file_exists($appPath)) {
            require_once $appPath;
            (new kmwaWaConfigHelper)->loadKmwaClasses(self::APP_ID);
        }
    }
}
