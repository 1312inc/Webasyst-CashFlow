<?php

/**
 * Class cashApiAggregateGetBreakDownDto
 */
final class cashApiAggregateGetBreakDownDto
{
    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $currency;

    /**
     * @var float
     */
    public $totalAmount = 0.0;

    /**
     * @var array|cashApiAggregateGetBreakDownDataDto[]
     */
    public $data;

    /**
     * cashApiAggregateGetBreakDownDto constructor.
     *
     * @param string                                      $currency
     * @param string                                      $type
     * @param array|cashApiAggregateGetBreakDownDataDto[] $data
     */
    public function __construct(string $currency, string $type, array $data)
    {
        $this->currency = $currency;
        $this->data = $data;
        $this->type = $type;
    }
}
