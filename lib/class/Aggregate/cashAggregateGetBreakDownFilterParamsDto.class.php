<?php

/**
 * Class cashAggregateGetBreakDownFilterParamsDto
 */
final class cashAggregateGetBreakDownFilterParamsDto
{
    public const GROUP_BY_DAY   = 'day';
    public const GROUP_BY_MONTH = 'month';
    public const GROUP_BY_YEAR  = 'year';

    public const DETAILS_BY_CONTACT  = 'contact';
    public const DETAILS_BY_CATEGORY = 'category';

    /**
     * @var cashAggregateFilter
     */
    public $filter;

    /**
     * @var DateTimeImmutable
     */
    public $from;

    /**
     * @var DateTimeImmutable
     */
    public $to;

    /**
     * @var waContact
     */
    public $contact;

    /**
     * @var string
     */
    public $detailsBy;

    /**
     * @var string[]
     */
    private $allowedDetails = [self::DETAILS_BY_CONTACT, self::DETAILS_BY_CATEGORY];

    /**
     * cashAggregateGetBreakDownFilterParamsDto constructor.
     *
     * @param                     $contact
     * @param                     $from
     * @param                     $to
     * @param                     $detailsBy
     * @param cashAggregateFilter $filter
     *
     * @throws kmwaRuntimeException
     * @throws waException
     */
    public function __construct($contact, $from, $to, $detailsBy, cashAggregateFilter $filter)
    {
        $this->from = $from;
        $this->to = $to;
        $this->contact = $contact;
        if (!in_array($detailsBy, $this->allowedDetails)) {
            throw new kmwaRuntimeException(
                sprintf('details_by %s is not allowed. Use %s', $detailsBy, implode(', ', $this->allowedDetails))
            );
        }
        $this->detailsBy = $detailsBy;
        $this->filter = $filter;
    }
}
