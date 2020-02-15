<?php

/**
 * Class cashRepeatingTransactionFactory
 */
class cashRepeatingTransactionFactory extends cashBaseFactory
{
    /**
     * @return cashRepeatingTransaction
     */
    public function createNew()
    {
        return (new cashRepeatingTransaction())->setCreateContactId(wa()->getUser()->getId());
    }


}
