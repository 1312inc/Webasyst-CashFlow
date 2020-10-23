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
    public $group_by = cashAggregateChartDataFilterParamsDto::GROUP_BY_DAY;

    /**
     * @var string
     */
    public $filter = '';
}
