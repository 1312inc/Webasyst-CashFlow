<?php

/**
 * Class cashCategoryDto
 */
class cashCategoryDto extends cashAbstractDto
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $color;

    /**
     * @var int
     */
    public $sort;

    /**
     * @var string
     */
    public $createDatetime;

    /**
     * @var bool
     */
    public $is_system = false;

    /**
     * @var bool
     */
    public $is_profit = false;

    /**
     * cashCategoryDto constructor.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        if ($data) {
            $this->initializeWithArray($data);
            $this->is_profit = (bool) $this->is_profit;
            $this->is_system = (bool) $this->is_system;
        } else {
            $this->name = _w('New category');
        }
    }
}