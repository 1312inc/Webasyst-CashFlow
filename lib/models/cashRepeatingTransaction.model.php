<?php

/**
 * Class cashRepeatingTransactionModel
 */
class cashRepeatingTransactionModel extends cashModel
{
    protected $table = 'cash_repeating_transaction';

    /**
     * @param string $source
     * @param string $hash
     *
     * @return bool|resource
     */
    public function deleteAllBySourceAndHash($source, $hash)
    {
        return $this->deleteByField(['external_source' => $source, 'external_hash' => $hash]);
    }

    /**
     * @param int $oldCategoryId
     * @param int $newCategoryId
     *
     * @return bool|waDbResultUpdate|null
     */
    public function changeCategoryId($oldCategoryId, $newCategoryId)
    {
        return $this->updateByField('category_id', $oldCategoryId, ['category_id' => $newCategoryId]);
    }
}
