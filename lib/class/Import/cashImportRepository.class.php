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
     * @param $providers
     * @return array
     * @throws waException
     */
    public function findLastN(int $n, $providers = ['csv']): array
    {
        return $this->findByQuery(
            $this->getModel()
                ->select('*')
                ->where('is_archived = 0')
                ->where('provider IN (?)', (array) $providers)
                ->order('id desc')
                ->limit($n)
        );
    }

    /**
     * @param $providers
     * @return array
     * @throws waException
     */
    public function findAllActive($providers = ['csv']): array
    {
        return $this->findByQuery(
            $this->getModel()
                ->select('*')
                ->where('is_archived = 0')
                ->where('provider IN (?)', (array) $providers)
        );
    }
}
