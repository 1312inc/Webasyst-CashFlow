<?php

/**
 * Class cashAccountRepository
 *
 * @method cashAccountModel getModel()
 */
class cashAccountRepository extends cashBaseRepository
{
    protected $entity = cashAccount::class;

    /**
     * @return cashAccount[]
     * @throws waException
     */
    public function findAllActive()
    {
        return $this->generateWithData($this->getModel()->getAllActive(), true);
    }
}
