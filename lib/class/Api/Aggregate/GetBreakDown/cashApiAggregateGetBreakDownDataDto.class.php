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
     * @var string
     */
    public $category_name;

    /**
     * @var string
     */
    public $category_color;

    /**
     * @var int
     */
    public $category_id;

    /**
     * cashApiAggregateGetBreakDownDto constructor.
     *
     * @param                   $amount
     * @param cashCategory|null $category
     */
    public function __construct($amount, ?cashCategory $category)
    {
        $this->amount = abs(round($amount, 2));
        if ($category) {
            $this->category_name = $category->getName();
            $this->category_color = $category->getColor();
            $this->category_id = $category->getId();
        }
    }
}
