<?php

/**
 * Class cashCheckinFactory
 */
class cashTransactionFactory extends cashBaseFactory
{
    /**
     * @return cashTransaction
     * @throws Exception
     */
    public function createNew()
    {
        return (new cashTransaction())
            ->setCreateDatetime(date('Y-m-d H:i:s'));
    }
}
