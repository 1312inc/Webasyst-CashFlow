<?php

final class cashApiAggregateGetBalanceFlowRequest
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
}
