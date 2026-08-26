<?php

/**
 * cashApiCompanyDeleteRequest
 */
class cashApiCompanyDeleteRequest
{
    /**
     * @var int
     */
    private $id;

    public function __construct($id)
    {
        $this->id = (int) $id;
    }

    public function getId()
    {
        return $this->id;
    }
}
