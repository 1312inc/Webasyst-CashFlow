<?php

final class cashApiTransactionBulkCompleteRequest
{
    private const MAX_IDS = 500;

    /**
     * @var array<int>
     */
    private $ids;

    /**
     * @param int[] $ids
     */
    public function __construct(array $ids) {
        if (count($ids) > self::MAX_IDS) {
            throw new cashValidateException(
                sprintf_wp('Too many transactions to move. Max limit is %d', self::MAX_IDS)
            );
        }

        array_walk($ids, 'intval');
        $ids = array_filter($ids, static function ($id) {
            return $id > 0;
        });

        $this->ids = $ids;
    }

    /**
     * @return int[]
     */
    public function getIds(): array
    {
        return $this->ids;
    }
}
