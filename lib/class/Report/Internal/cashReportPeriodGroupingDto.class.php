<?php

final class cashReportPeriodGroupingDto
{
    public $name;
    public $date;
    public $key;

    public function __construct(string $name, $date, $key)
    {
        $this->name = $name;
        $this->date = $date;
        $this->key = $key;
    }
}
