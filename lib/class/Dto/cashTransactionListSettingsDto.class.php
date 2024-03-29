<?php

/**
 * Class cashTransactionListSettingsDto
 */
class cashTransactionListSettingsDto implements JsonSerializable
{
    use cashDtoJsonSerializableTrait;

    /**
     * @var bool
     */
    public $addIncome = true;

    /**
     * @var bool
     */
    public $addExpense = true;

    /**
     * @var bool
     */
    public $addTransfer = true;

    /**
     * @var bool
     */
    public $addHandler = true;

    /**
     * @var bool
     */
    public $bulkActions = true;

    /**
     * @var bool
     */
    public $showOnHandsEndday = true;

    /**
     * @var bool
     */
    public $allowEdit = true;

    /**
     * cashTransactionListSettingsDto constructor.
     *
     * @param cashTransactionPageFilterDto|null $filter
     * @param cashGraphPeriodVO|null            $forecastPeriod
     */
    public function __construct(cashTransactionPageFilterDto $filter = null, cashGraphPeriodVO $forecastPeriod = null)
    {
        if ($filter instanceof cashTransactionPageFilterDto) {
            $this->showOnHandsEndday = $filter->type === cashTransactionPageFilterDto::FILTER_ACCOUNT;/* && FORECAST_IS_SHOWN*/
            if ($forecastPeriod && $forecastPeriod->getValue() <= 0) {
                $this->showOnHandsEndday = false;
            }
            $this->addIncome = ($filter->type === cashTransactionPageFilterDto::FILTER_ACCOUNT)
                || ($filter->type === cashTransactionPageFilterDto::FILTER_CATEGORY && $filter->entity->isIncome());
            $this->addExpense = ($filter->type === cashTransactionPageFilterDto::FILTER_ACCOUNT)
                || ($filter->type === cashTransactionPageFilterDto::FILTER_CATEGORY && $filter->entity->isExpense());
            $this->addTransfer = $filter->type === cashTransactionPageFilterDto::FILTER_ACCOUNT;
            $this->bulkActions = in_array(
                $filter->type,
                [cashTransactionPageFilterDto::FILTER_ACCOUNT, cashTransactionPageFilterDto::FILTER_CATEGORY],
                true
            );
            $this->addHandler = $this->addTransfer || $this->addExpense || $this->addIncome;
        }
    }
}
