<?php

/**
 * Class cashApiAggregateGetChartDataRequest
 */
class cashApiAggregateGetChartDataRequest
{
    /**
     * @var string|DateTimeImmutable
     */
    public $from = '';

    /**
     * @var string|DateTimeImmutable
     */
    public $to = '';

    /**
     * @var string
     */
    public $group_by = cashAggregateFilterParamsDto::GROUP_BY_DAY;

    /**
     * @var int
     */
    public $account_id;

    /**
     * @var int
     */
    public $category_id;

    /**
     * @var string|cashCurrencyVO
     */
    public $currency;
}
