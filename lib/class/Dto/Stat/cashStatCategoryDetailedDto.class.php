<?php

/**
 * Class cashStatCategoryDetailedDto
 */
class cashStatCategoryDetailedDto
{
    /**
     * @var cashCategoryDto
     */
    public $category;

    /**
     * @var cashStatOnHandDto[]
     */
    public $stat = [];

    /**
     * cashStatOnHandDto constructor.
     *
     * @param cashCategoryDto   $category
     */
    public function __construct(cashCategoryDto $category)
    {
        $this->category = $category;
    }
}
