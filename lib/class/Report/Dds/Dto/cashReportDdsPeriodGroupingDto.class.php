<?php

/**
 * Class cashReportDdsPeriodGroupingDto
 */
class cashReportDdsPeriodGroupingDto
{
    public $name;
    public $date;
    public $key;

    /**
     * cashReportDdsPeriodGroupingDto constructor.
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
