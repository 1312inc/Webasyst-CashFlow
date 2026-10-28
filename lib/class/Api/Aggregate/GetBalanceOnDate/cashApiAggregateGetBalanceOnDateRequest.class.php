<?php

final class cashApiAggregateGetBalanceOnDateRequest
{
    /**
     * @var string|DateTimeImmutable
     */
    public $date = '';

    /**
     * @var string
     */
    public $filter = '';

    /**
     * @var null|int
     */
    public $scenario_id;
}
