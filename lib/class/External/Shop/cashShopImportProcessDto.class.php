<?php

final class cashShopImportProcessDto implements JsonSerializable
{
    public $totalOrders = 0;

    public $passedOrders = 0;

    public $time = 0;

    public $memory = 0;

    public $processId;

    public $done = false;

    public $error = '';

    public $incomeTransactions = 0;

    public $expenseTransactions = 0;

    public $period = 'all';

    /**
     * @var DateTime|null
     */
    public $periodAfter;

    /**
     * cashShopImportProcessDto constructor.
     *
     * @param $processId
     */
    public function __construct($processId)
    {
        $this->processId = $processId;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'total_orders' => $this->totalOrders,
            'passed_orders' => $this->passedOrders,
            'time' => $this->time,
            'memory' => $this->memory,
            'processId' => $this->processId,
            'ready' => $this->done,
            'progress' => min(100, round($this->passedOrders / $this->totalOrders * 100)),
            'error' => $this->error,
        ];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) json_encode($this, JSON_UNESCAPED_UNICODE);
    }
}
