<?php

final class cashApiTransactionBulkMoveRequest
{
    private const MAX_IDS = 500;

    /**
     * @var array<int>
     */
    private $ids;

    /**
     * @var null|int
     */
    private $categoryId;

    /**
     * @var null|int
     */
    private $accountId;

    /**
     * @var null|int
     */
    private $contractorContactId;

    /**
     * @var null|string
     */
    private $contractorName;

    /**
     * @param int[] $ids
     */
    public function __construct(
        array $ids,
        ?int $categoryId,
        ?int $accountId,
        ?int $contractorContactId,
        ?string $contractorName
    ) {
        if (empty($categoryId) && empty($accountId) && empty($contractorContactId) && empty($contractorName)) {
            throw new cashValidateException(_w('Nothing to move. No params'));
        }

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
        $this->categoryId = $categoryId;
        $this->accountId = $accountId;
        $this->contractorContactId = $contractorContactId;
        $this->contractorName = $contractorName;
    }

    /**
     * @return int[]
     */
    public function getIds(): array
    {
        return $this->ids;
    }

    public function getCategoryId(): ?int
    {
        return $this->categoryId;
    }

    public function getAccountId(): ?int
    {
        return $this->accountId;
    }

    public function getContractorContactId(): ?int
    {
        return $this->contractorContactId;
    }

    public function getContractorName(): ?string
    {
        return $this->contractorName;
    }
}
