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

    public function __construct(int $id, string $name, string $img, cashReportClientsAbcValueDto $value)
    {
        $this->id = $id;
        $this->name = $name;
        $this->img = $img;
        $this->value = $value;
    }
}
