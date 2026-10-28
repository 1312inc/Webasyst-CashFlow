<?php

/**
 * cashApiScenarioCreateRequest
 */
class cashApiScenarioCreateRequest
{
    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var string|null
     */
    protected $color;

    /**
     * @var int
     */
    protected $sort;

    /**
     * @param $name
     * @param $color
     * @param $sort
     */
    public function __construct($name, $color, $sort)
    {
        $this->name = $name;
        $this->color = $color;
        $this->sort = $sort;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @return int
     */
    public function getSort()
    {
        return $this->sort;
    }
}
