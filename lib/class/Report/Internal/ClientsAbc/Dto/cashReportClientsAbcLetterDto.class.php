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

    public function __construct(string $letter, cashReportClientsAbcValueDto $value)
    {
        $this->letter = $letter;
        $this->value = $value;
    }
}
