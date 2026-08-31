<?php

/**
 * cashApiScenarioDeleteRequest
 */
class cashApiScenarioDeleteRequest
{
    /**
     * @var int
     */
    private $id;

    /**
     * @param $id
     */
    public function __construct($id)
    {
        $this->id = (int) $id;
    }

    public function getId()
    {
        return $this->id;
    }
}
