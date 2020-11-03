<?php

/**
 * Class cashApiAggregateGetBreakDownDto
 */
final class cashApiAggregateGetBreakDownDataDto
{
    /**
     * @var float
     */
    public $amount;

    /**
     * @var int
     */
    public $category_id;

    /**
     * cashApiAggregateGetBreakDownDto constructor.
     *
     * @param $amount
     * @param $category_id
     */
    public function __construct($amount, $category_id)
    {
        $this->amount = abs(round($amount, 2));
        $this->category_id = (int) $category_id;
    }
}
