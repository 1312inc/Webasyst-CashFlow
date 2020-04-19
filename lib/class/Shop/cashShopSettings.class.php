<?php

/**
 * Class cashShopSettings
 */
class cashShopSettings implements JsonSerializable
{
    use cashDtoJsonSerializableTrait;

    const AUTO_DAILY_SALES = 1;
    const MANUAL_DAILY_SALES = 2;

    /**
     * @var bool
     */
    private $enabled = 0;

    /**
     * @var int
     */
    private $accountId = 0;

    /**
     * @var array
     */
    private $accountByStorefronts = [];

    /**
     * @var int
     */
    private $categoryIncomeId = 0;

    /**
     * @var int
     */
    private $categoryExpenseId = 0;

    /**
     * @var bool
     */
    private $enableForecast = 0;

    /**
     * @var bool
     */
    private $autoForecast = 0;

    /**
     * @var int
     */
    private $manualForecast = 0;

    /**
     * @var bool
     */
    private $writeToOrderLog = 0;

    /**
     * @var array
     */
    private $savedSettings = [];

    /**
     * @var waAppSettingsModel
     */
    private $settingsModel;

    /**
     * @var array
     */
    private $incomeActions = ['pay', 'restore', 'complete'];

    /**
     * @var array
     */
    private $expenseActions = ['refund', 'delete', 'cancel'];

    /**
     * @var string[]
     */
    private $jsonSerializableProperties = [
        'enabled',
        'accountId',
        'accountByStorefronts',
        'categoryIncomeId',
        'categoryExpenseId',
        'enableForecast',
        'autoForecast',
        'manualForecast',
        'writeToOrderLog',
        'incomeActions',
        'expenseActions',
    ];

    /**
     * cashShopScriptSettings constructor.
     */
    public function __construct()
    {
        $this->settingsModel = new waAppSettingsModel();
        $this->savedSettings = json_decode($this->settingsModel->get(cashConfig::APP_ID, 'shopscript_integration'), true) ?: [];
        $this->load($this->savedSettings);
    }

    /**
     * @param array $data
     *
     * @return cashShopSettings
     */
    public function load(array $data)
    {
        $data = array_merge($this->savedSettings, $data);
        foreach (get_class_vars(__CLASS__) as $varname => $value) {
            if (!in_array($varname, $this->jsonSerializableProperties)) {
                continue;
            }

            $this->$varname = ifset($data, $varname, $value);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     *
     * @return cashShopSettings
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return int
     */
    public function getAccountId()
    {
        return $this->accountId;
    }

    /**
     * @param string $storefront
     *
     * @return int
     */
    public function getAccountByStorefront($storefront)
    {
        return isset($this->accountByStorefronts[$storefront]) ? $this->accountByStorefronts[$storefront] : 0;
    }

    /**
     * @return int
     */
    public function getCategoryIncomeId()
    {
        return $this->categoryIncomeId;
    }

    /**
     * @return int
     */
    public function getCategoryExpenseId()
    {
        return $this->categoryExpenseId;
    }

    /**
     * @return bool
     */
    public function isEnableForecast()
    {
        return $this->enableForecast;
    }

    /**
     * @return int
     */
    public function getManualForecast()
    {
        return $this->manualForecast;
    }

    /**
     * @return bool
     */
    public function isWriteToOrderLog()
    {
        return $this->writeToOrderLog;
    }

    public function save()
    {
        $this->settingsModel->set(
            cashConfig::APP_ID,
            'shopscript_integration',
            json_encode($this, JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * @return bool
     */
    public function isAutoForecast()
    {
        return $this->autoForecast;
    }

    /**
     * @return array
     */
    public function getIncomeActions()
    {
        return $this->incomeActions;
    }

    /**
     * @return array
     */
    public function getExpenseActions()
    {
        return $this->expenseActions;
    }
}
