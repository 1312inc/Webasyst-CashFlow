<?php

final class cashApiTransactionPurgeRequest
{
    /**
     * @var array<int>
     */
    private $ids = [];

    /**
     * @param int[] $ids
     */
    public function __construct(array $ids)
    {
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
