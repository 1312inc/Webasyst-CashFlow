<?php

class cashReportClientsAbcLetterDto
{
    /**
     * @var string
     */
    public $letter;

    /**
     * @var cashReportClientsAbcValueDto
     */
    public $value;

    /**
     * @var array
     */
    public $clients = [];

    /**
     * @var float
     */
    public $targetPercent;

    public function __construct(float $percent, string $letter, cashReportClientsAbcValueDto $value)
    {
        $this->letter = $letter;
        $this->value = $value;
        $this->targetPercent = $percent;
    }
}
