<?php

/**
 * Class cashDdsPluginTypeDto
 */
class cashDdsPluginTypeDto
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var bool
     */
    public $hasChart;

    /**
     * @var int
     */
    public $incomeEntities = 0;

    /**
     * @var int
     */
    public $expenseEntities = 0;

    /**
     * cashDdsPluginTypeDto constructor.
     *
     * @param string $id
     * @param string $name
     * @param bool   $hasChart
     * @param int    $incomeEntities
     * @param int    $expenseEntities
     */
    public function __construct($id, $name, $hasChart, $incomeEntities = 0, $expenseEntities = 0)
    {
        $this->id = $id;
        $this->name = $name;
        $this->hasChart = $hasChart;
        $this->incomeEntities = $incomeEntities;
        $this->expenseEntities = $expenseEntities;
    }
}
