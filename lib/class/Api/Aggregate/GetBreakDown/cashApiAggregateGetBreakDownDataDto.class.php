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

    public $children_amount;

    /**
     * cashApiAggregateGetBreakDownDto constructor.
     *
     * @param $data
     * @param cashCategory|null $category
     */
    public function __construct($data, ?cashCategory $category)
    {
        $amount = ifset($data, 'amount', 0);
        $this->amount = abs(round($amount, 2));
        if (!$this->children_amount = ifset($data, 'children_amount', null)) {
            unset($this->children_amount);
        }
        if ($category) {
            $this->category_name = $category->getName();
            $this->category_color = $category->getColor();
            $this->category_id = $category->getId();
        }
    }
}
