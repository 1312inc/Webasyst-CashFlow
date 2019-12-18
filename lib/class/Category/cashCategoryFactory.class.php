<?php

/**
 * Class cashCheckinFactory
 */
class cashCategoryFactory extends cashBaseFactory
{
    /**
     * @return cashCategory
     * @throws Exception
     */
    public function createNew()
    {
        return (new cashCategory)
            ->setCreateDatetime(date('Y-m-d H:i:s'));
    }
}
