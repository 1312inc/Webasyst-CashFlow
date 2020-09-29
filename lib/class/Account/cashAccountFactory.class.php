<?php

/**
 * Class cashAccountFactory
 */
class cashAccountFactory extends cashBaseFactory
{
    /**
     * @return cashAccount
     * @throws Exception
     */
    public function createNew()
    {
        return (new cashAccount)
            ->setCreateDatetime(date('Y-m-d H:i:s'));
    }

    /**
     * @return cashAccount
     */
    public function createAllAccount()
    {
        return (new cashAccount())
            ->setName(_w('Cash on hand today'));
    }
}
