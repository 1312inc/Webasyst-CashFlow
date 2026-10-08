<?php

/**
 * cashApiScenarioUpdateRequest
 */
class cashApiScenarioUpdateRequest extends cashApiScenarioCreateRequest
{
    /**
     * @var int
     */
    private $id;

    /**
     * @param int $id
     * @param string $name
     * @param string $color
     * @param int $sort
     */
    public function __construct($id, $name, $color, $sort)
    {
        $this->id = (int) $id;
        parent::__construct($name, $color, $sort);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
}
