<?php


class cashAggregateFilterParamsDto
{
    public const GROUP_BY_DAY   = 'day';
    public const GROUP_BY_MONTH = 'month';
    public const GROUP_BY_YEAR  = 'year';

    public $accountId;

    public $categoryId;

    /**
     * @var DateTimeImmutable
     */
    public $from;

    /**
     * @var DateTimeImmutable
     */
    public $to;

    public $groupBy;

    /**
     * @var waContact
     */
    public $contact;

    public $currency;

    public function __construct($contact, $accountId, $categoryId, $from, $to, $groupBy, $currency)
    {
        $this->accountId = $accountId;
        $this->categoryId = $categoryId;
        $this->from = $from;
        $this->to = $to;
        $this->groupBy = $groupBy;
        $this->contact = $contact;
        $this->currency = $currency;
    }
}
