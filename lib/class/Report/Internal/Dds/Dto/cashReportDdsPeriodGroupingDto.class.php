<?php

final class cashReportDdsPeriodGroupingDto
{
    public $name;
    public $date;
    public $key;

    /**
     * cashReportDdsServicePeriodGroupingDto constructor.
     *
     * @param $name
     * @param $date
     * @param $key
     */
    public function __construct($name, $date, $key)
    {
        $this->name = $name;
        $this->date = $date;
        $this->key = $key;
    }
}
