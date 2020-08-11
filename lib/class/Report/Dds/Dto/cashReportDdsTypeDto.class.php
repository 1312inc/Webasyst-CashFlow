<?php

/**
 * Class cashReportDdsTypeDto
 */
class cashReportDdsTypeDto
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
     * cashReportDdsTypeDto constructor.
     *
     * @param string $id
     * @param string $name
     */
    public function __construct($id, $name)
    {
        $this->id = $id;
        $this->name = $name;
    }
}
