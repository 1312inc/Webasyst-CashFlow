<?php

/**
 * cashApiCompanyUpdateRequest
 */
class cashApiCompanyUpdateRequest extends cashApiCompanyCreateRequest
{
    /**
     * @var int
     */
    private $id;

    /**
     * @param int $id
     * @param string $name
     * @param int $sort
     */
    public function __construct($id, $name, $sort)
    {
        $this->id = (int) $id;
        parent::__construct($name, $sort);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
}
