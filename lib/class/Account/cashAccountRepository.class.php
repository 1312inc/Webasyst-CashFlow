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

    /**
     * @return cashAccount
     * @throws waException
     */
    public function findFirst()
    {
        return $this->findByQuery($this->getModel()->select('*')->order('id')->limit(1), false);
    }
}
