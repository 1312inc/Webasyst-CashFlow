<?php

class cashReportClientsAbcValueDto
{
    /**
     * @var float
     */
    public $amount;

    /**
     * @var float
     */
    public $percent;

    public function __construct(float $amount, float $percent)
    {
        $this->amount = $amount;
        $this->percent = $percent;
    }
}
