<?php

/**
 * Class cashTransactionFactory
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
            ->setCreateDatetime(date('Y-m-d H:i:s'))
            ->setDatetime(date('Y-m-d H:i:s'))
            ->setCreateContactId(wa()->getUser()->getId())
            ->setDate(date('Y-m-d'));
    }
}
