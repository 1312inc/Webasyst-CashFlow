<?php

/**
 * Class cashAggregateGetBreakDownFilterParamsDto
 */
final class cashAggregateGetBreakDownFilterParamsDto
{
    public const GROUP_BY_DAY   = 'day';
    public const GROUP_BY_MONTH = 'month';
    public const GROUP_BY_YEAR  = 'year';

    public const DETAILS_BY_CONTACT = 'contact';
    public const DETAILS_BY_CATEGORY = 'category';

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
    public  $contact;

    public $detailsBy;

    public function __construct($contact, $from, $to, $groupBy, $detailsBy)
    {
        $this->from = $from;
        $this->to = $to;
        $this->groupBy = $groupBy;
        $this->contact = $contact;
        $this->detailsBy = $detailsBy;
    }
}
