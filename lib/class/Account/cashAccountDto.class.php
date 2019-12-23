<?php

/**
 * Class cashAccountDto
 */
class cashAccountDto extends cashAbstractDto
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $description = '';

    /**
     * @var string
     */
    public $icon = '';

    /**
     * @var string
     */
    public $currency;

    /**
     * @var float
     */
    public $currentBalance = 0.0;

    /**
     * @var int
     */
    public $customerContactId;

    /**
     * @var bool
     */
    public $isArchived;

    /**
     * @var int
     */
    public $sort;

    /**
     * @var string
     */
    public $createDatetime;

    /**
     * @var string
     */
    public $updateDatetime = '';

    /**
     * cashAccountDto constructor.
     */
    public function __construct()
    {
        $this->name = _w('New account');
    }
}
