<?php

class cashApiTransactionRestoreDto
{
    /**
     * @var array<int>
     */
    private $ok;

    /**
     * @var array<int>
     */
    private $fail;

    /**
     * @param int[] $ok
     * @param int[] $fail
     */
    public function __construct(array $ok, array $fail)
    {
        $this->ok = array_map('intval', $ok);
        $this->fail = array_map('intval', $fail);
    }

    /**
     * @return int[]
     */
    public function getOk(): array
    {
        return $this->ok;
    }

    /**
     * @return int[]
     */
    public function getFail(): array
    {
        return $this->fail;
    }
}
