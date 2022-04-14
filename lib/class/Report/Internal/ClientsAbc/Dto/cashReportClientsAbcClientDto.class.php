<?php

class cashReportClientsAbcClientDto
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $img;

    /**
     * @var cashReportClientsAbcValueDto
     */
    public $value;

    public function __construct(string $name, string $img, cashReportClientsAbcValueDto $value)
    {
        $this->name = $name;
        $this->img = $img;
        $this->value = $value;
    }
}
