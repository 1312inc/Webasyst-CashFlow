<?php

/**
 * Class cashShopImportSettingsDto
 */
class cashShopImportSettingsDto
{
    const ORDERS_TO_PROCEED = 30;

    /**
     * @var int
     */
    public $accountId;

    /**
     * @var int
     */
    public $categoryIncomeId;

    /**
     * @var int
     */
    public $chunk = self::ORDERS_TO_PROCEED;

    /**
     * cashShopImportSettingsDto constructor.
     *
     * @param int $accountId
     * @param int $categoryIncomeId
     */
    public function __construct($accountId, $categoryIncomeId)
    {
        $this->accountId = $accountId;
        $this->categoryIncomeId = $categoryIncomeId;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return serialize($this);
    }
}
