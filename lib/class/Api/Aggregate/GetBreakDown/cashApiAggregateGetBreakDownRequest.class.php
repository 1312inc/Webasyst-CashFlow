<?php

/**
 * Class cashApiAggregateGetBreakDownRequest
 */
class cashApiAggregateGetBreakDownRequest
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
    public $details_by = cashAggregateChartDataFilterParamsDto::GROUP_BY_DAY;

    /**
     * @var string
     */
    public $filter;
}
