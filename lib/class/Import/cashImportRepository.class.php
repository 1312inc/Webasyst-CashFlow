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
    public function findLastN(int $n): array
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
    public function findAllActive(): array
    {
        return $this->findByQuery(
            $this->getModel()
                ->select('*')
                ->where('is_archived = 0'),
            true
        );
    }
}
