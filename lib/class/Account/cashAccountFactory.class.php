<?php

/**
 * Class cashCheckinFactory
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
}
