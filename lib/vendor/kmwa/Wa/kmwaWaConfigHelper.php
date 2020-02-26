<?php

/**
 * Class kmwaWaConfigHelper
 */
class kmwaWaConfigHelper
{
    public function loadKmwaClasses($appId)
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
                'kmwaRuntimeException',
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
            'lib/vendor/kmwa/Trait' => [
                'kmwaEntityDatetimeTrait',
            ],
        ];

        foreach ($customClasses as $path => $classes) {
            foreach ($classes as $class) {
                $file = wa()->getAppPath(sprintf('%s/%s.php', $path, $class), $appId);
                if (file_exists($file) && !(class_exists($class, false) && trait_exists($class, false))) {
                    waAutoload::getInstance()->add(
                        $class,
                        sprintf('wa-apps/%s/%s/%s.php', $appId, $path, $class)
                    );
                }
            }
        }
    }
}
