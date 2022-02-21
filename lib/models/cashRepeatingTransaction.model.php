<?php

class cashRepeatingTransactionModel extends cashModel
{
    protected $table = 'cash_repeating_transaction';

    public function deleteAllSelfDestructBySource(string $source): bool
    {
        return $this->deleteByField(['external_source' => $source, 'is_self_destruct_when_due' => 1]);
    }

    /**
     * @return bool|waDbResultUpdate|null
     */
    public function changeCategoryId(int $oldCategoryId, int $newCategoryId)
    {
        return $this->updateByField('category_id', $oldCategoryId, ['category_id' => $newCategoryId]);
    }
}
