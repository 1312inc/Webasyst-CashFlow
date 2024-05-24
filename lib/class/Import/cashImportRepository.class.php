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
     * @param $provider
     * @return array
     * @throws waException
     */
    public function findLastN(int $n, $provider = 'csv'): array
    {
        return $this->findByQuery(
            $this->getModel()
                ->select('*')
                ->where('is_archived = 0')
                ->where('provider = ?', $provider)
                ->order('id desc')
                ->limit($n)
        );
    }

    /**
     * @param $provider
     * @return array
     * @throws waException
     */
    public function findAllActive($provider = 'csv'): array
    {
        return $this->findByQuery(
            $this->getModel()
                ->select('*')
                ->where('is_archived = 0')
                ->where('provider = ?', $provider)
        );
    }
}
