<?php

/**
 * Class cashEventSettingsSave
 *
 * @property cashShopSettings $object
 */
class cashEventSettingsSave extends cashEvent
{
    /**
     * @var cashShopSettings
     */
    private $newSettings;

    /**
     * cashEventSettingsSave constructor.
     *
     * @param cashShopSettings $oldSettings
     * @param array            $params
     */
    public function __construct(cashShopSettings $oldSettings, $params = [])
    {
        parent::__construct(cashEventStorage::SETTINGS_SAVE, clone $oldSettings, $params);
    }

    /**
     * @param cashShopSettings $newSettings
     *
     * @return cashEventSettingsSave
     */
    public function setNewSettings($newSettings)
    {
        $this->newSettings = $newSettings;

        return $this;
    }

    /**
     * @return cashShopSettings
     */
    public function getNewSettings()
    {
        return $this->newSettings;
    }

    /**
     * @return cashShopSettings
     */
    public function getOldSettings()
    {
        return $this->object;
    }
}
