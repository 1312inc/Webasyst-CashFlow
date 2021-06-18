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
     * @var cashAccount
     */
    private $account;

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
    private $enableForecast = 1;

    /**
     * @var bool
     */
    private $autoForecast = 1;

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
     * @var bool
     */
    private $forecastActualizedToday = false;

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
        'categoryPurchaseId',
        'categoryShippingId',
        'categoryTaxId',
        'enableForecast',
        'autoForecast',
        'manualForecast',
        'writeToOrderLog',
        'incomeActions',
        'expenseActions',
    ];

    /**
     * @var array|mixed
     */
    private $stat;

    /**
     * @var int
     */
    private $todayTransactions = 0;

    /**
     * @var int|null
     */
    private $categoryPurchaseId;

    /**
     * @var int|null
     */
    private $categoryShippingId;

    /**
     * @var int|null
     */
    private $categoryTaxId;

    /**
     * @var cashCategoryFactory
     */
    private $categoryFactory;

    /**
     * @var cashCategoryRepository
     */
    private $categoryRepository;

    /**
     * @var bool
     */
    private $firstTime = true;

    /**
     * @var array
     */
    private $errors = [];

    /**
     * cashShopScriptSettings constructor.
     */
    public function __construct()
    {
        $this->settingsModel = new waAppSettingsModel();
        $this->savedSettings = json_decode(
            $this->settingsModel->get(cashConfig::APP_ID, 'shopscript_integration'),
            true
        ) ?: [];

        $this->firstTime = filter_var(
            $this->settingsModel->get(cashConfig::APP_ID, 'shopscript_integration_first_time', true),
            FILTER_VALIDATE_BOOLEAN
        );
        $this->load($this->savedSettings);

        $statData = json_decode($this->settingsModel->get(cashConfig::APP_ID, 'shopscript_stat'), true) ?: [];
        $this->todayTransactions = ifset($statData, 'today_transactions', date('Y-m-d'), $this->todayTransactions);
        $this->forecastActualizedToday = !empty($statData['forecast_actualized_today'])
            && $statData['forecast_actualized_today'] == date('Y-m-d');

        $this->categoryFactory = cash()->getEntityFactory(cashCategory::class);
        $this->categoryRepository = cash()->getEntityRepository(cashCategory::class);
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    public function validate(array $data):bool
    {
        if (empty($data['categoryIncomeId'])) {
            $this->errors['categoryIncomeId'] = _w('Sales category must be set');
        }

        if (empty($data['categoryExpenseId'])) {
            $this->errors['categoryExpenseId'] = _w('Refund category must be set');
        }

        return empty($this->errors);
    }

    /**
     * @param array $data
     *
     * @return cashShopSettings
     */
    public function load(array $data): cashShopSettings
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
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
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
     * @return bool
     */
    public function isForecastActualizedToday()
    {
        return $this->forecastActualizedToday;
    }

    /**
     * @param bool $forecastActualizedToday
     *
     * @return cashShopSettings
     */
    public function setForecastActualizedToday($forecastActualizedToday)
    {
        $this->forecastActualizedToday = $forecastActualizedToday;

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
     * @return array
     */
    public function getAccountsWithStorefront()
    {
        return $this->accountByStorefronts;
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
     * @return cashCategory
     */
    public function getCategoryIncome()
    {
        return $this->findCategoryOrCreateNoCategory($this->categoryIncomeId);
    }

    /**
     * @return cashCategory
     */
    public function getCategoryExpense()
    {
        return $this->findCategoryOrCreateNoCategory($this->categoryExpenseId);
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

    public function saveFirstTime()
    {
        $this->settingsModel->set(
            cashConfig::APP_ID,
            'shopscript_integration_first_time',
            (int)$this->isFirstTime()
        );
    }

    public function saveStat()
    {
        $this->settingsModel->set(
            cashConfig::APP_ID,
            'shopscript_stat',
            json_encode(
                [
                    'today_transactions' => [date('Y-m-d') => $this->todayTransactions],
                    'forecast_actualized_today' => $this->forecastActualizedToday ? date('Y-m-d') : 0,
                ]
            )
        );
    }

    public function resetSettings()
    {
        $this->settingsModel->del(cashConfig::APP_ID, 'shopscript_integration_first_time');
        $this->settingsModel->del(cashConfig::APP_ID, 'shopscript_integration');
        $this->settingsModel->del(cashConfig::APP_ID, 'shopscript_stat');
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

    /**
     * @return int
     */
    public function getTodayTransactions()
    {
        return (int)$this->todayTransactions;
    }

    /**
     * @param int $inc
     *
     * @return $this
     */
    public function incTodayTransactionsCount($inc = 1)
    {
        $this->todayTransactions += $inc;

        return $this;
    }

    /**
     * @return bool
     */
    public function isTurnedOn()
    {
        return $this->enabled && empty($this->savedSettings['enabled']);
    }

    /**
     * @return bool
     */
    public function isTurnedOff()
    {
        return !$this->enabled && !empty($this->savedSettings['enabled']);
    }

    /**
     * @return bool
     */
    public function forecastTurnedOn()
    {
        return $this->enableForecast && empty($this->savedSettings['enableForecast']);
    }

    /**
     * @return bool
     */
    public function forecastTurnedOff()
    {
        return !$this->enableForecast && !empty($this->savedSettings['enableForecast']);
    }

    /**
     * @return bool
     */
    public function forecastTypeChanged(): bool
    {
        return $this->enableForecast && ($this->savedSettings['autoForecast'] != $this->autoForecast || $this->savedSettings['manualForecast'] != $this->manualForecast);
    }

    /**
     * @return bool
     */
    public function forecastAccountChanged(): bool
    {
        return $this->enableForecast && $this->savedSettings['accountId'] != $this->accountId;
    }

    /**
     * @return bool
     */
    public function forecastCategoryIncomeChanged(): bool
    {
        return $this->enableForecast && $this->savedSettings['categoryIncomeId'] != $this->categoryIncomeId;
    }

    /**
     * @return $this
     */
    public function resetStat(): self
    {
        $this->forecastActualizedToday = false;
        $this->todayTransactions = 0;

        return $this;
    }

    /**
     * @param int $accountId
     *
     * @return cashShopSettings
     */
    public function setAccountId($accountId)
    {
        $this->accountId = $accountId;

        return $this;
    }

    /**
     * @param int $categoryIncomeId
     *
     * @return cashShopSettings
     */
    public function setCategoryIncomeId($categoryIncomeId)
    {
        $this->categoryIncomeId = $categoryIncomeId;

        return $this;
    }

    /**
     * @param int $categoryExpenseId
     *
     * @return cashShopSettings
     */
    public function setCategoryExpenseId($categoryExpenseId)
    {
        $this->categoryExpenseId = $categoryExpenseId;

        return $this;
    }

    /**
     * @param bool $writeToOrderLog
     *
     * @return cashShopSettings
     */
    public function setWriteToOrderLog($writeToOrderLog)
    {
        $this->writeToOrderLog = $writeToOrderLog;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getCategoryPurchaseId()
    {
        return $this->categoryPurchaseId;
    }

    /**
     * @return cashCategory
     */
    public function getCategoryPurchase()
    {
        return $this->findCategoryOrCreateNoCategory($this->categoryPurchaseId);
    }

    /**
     * @param int|null $categoryPurchaseId
     *
     * @return cashShopSettings
     */
    public function setCategoryPurchaseId($categoryPurchaseId)
    {
        $this->categoryPurchaseId = $categoryPurchaseId;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getCategoryShippingId()
    {
        return $this->categoryShippingId;
    }

    /**
     * @return cashCategory
     */
    public function getCategoryShipping()
    {
        return $this->findCategoryOrCreateNoCategory($this->categoryShippingId);
    }

    /**
     * @param int|null $categoryShippingId
     *
     * @return cashShopSettings
     */
    public function setCategoryShippingId($categoryShippingId)
    {
        $this->categoryShippingId = $categoryShippingId;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getCategoryTaxId()
    {
        return $this->categoryTaxId;
    }

    /**
     * @return cashCategory
     */
    public function getCategoryTax()
    {
        return $this->findCategoryOrCreateNoCategory($this->categoryTaxId);
    }

    /**
     * @param int|null $categoryTaxId
     *
     * @return cashShopSettings
     */
    public function setCategoryTaxId($categoryTaxId)
    {
        $this->categoryTaxId = $categoryTaxId;

        return $this;
    }

    /**
     * @return bool
     */
    public function isFirstTime(): bool
    {
        return $this->firstTime;
    }

    /**
     * @param bool $firstTime
     *
     * @return cashShopSettings
     */
    public function setFirstTime(bool $firstTime): cashShopSettings
    {
        $this->firstTime = $firstTime;

        return $this;
    }

    /**
     * @param $id
     *
     * @return cashCategory
     * @throws waException
     */
    private function findCategoryOrCreateNoCategory($id): cashCategory
    {
        return $this->categoryRepository->findById($id) ?: $this->categoryFactory->createNewNoCategory();
    }

    /**
     * @return cashAccount
     */
    public function getAccount(): cashAccount
    {
        if ($this->account === null) {
            $this->account = cash()->getEntityRepository(cashAccount::class)->findById($this->accountId);
            kmwaAssert::instance($this->account, cashAccount::class);
        }

        return $this->account;
    }
}
