<?php

/**
 * cashApiCompanyCreateRequest
 */
class cashApiCompanyCreateRequest
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $sort;

    public function __construct($name, $sort)
    {
        $this->name = (string) $name;
        $this->sort = (int) $sort;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getSort(): int
    {
        return $this->sort;
    }
}
