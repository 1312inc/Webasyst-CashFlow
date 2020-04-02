<?php

/**
 * Class cashImportRepository
 *
 * @method cashImportModel getModel()
 */
class cashImportRepository extends cashBaseRepository
{
    protected $entity = cashImport::class;

    /**
     * @param int $n
     *
     * @return cashImport[]
     * @throws waException
     */
    public function findLastN($n)
    {
        return $this->findByQuery(
            $this->getModel()
                ->select('*')
                ->where('is_archived = 0')
                ->order('id desc')
                ->limit((int)$n),
            true
        );
    }

    /**
     * @return cashImport[]
     * @throws waException
     */
    public function findAllActive()
    {
        return $this->findByQuery(
            $this->getModel()
                ->select('*')
                ->where('is_archived = 0'),
            true
        );
    }
}
