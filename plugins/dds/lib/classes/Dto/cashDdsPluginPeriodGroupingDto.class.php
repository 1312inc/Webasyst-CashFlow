<?php

/**
 * Class cashDdsPluginPeriodGroupingDto
 */
class cashDdsPluginPeriodGroupingDto
{
    public $name;
    public $date;
    public $key;

    /**
     * cashDdsPluginPeriodGroupingDto constructor.
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
