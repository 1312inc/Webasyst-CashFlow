<?php

/**
 * Class cashTransactionFilterParamsDto
 */
class cashTransactionFilterParamsDto
{
    /**
     * @var DateTimeImmutable|null
     */
    public $startDate;

    /**
     * @var DateTimeImmutable|null
     */
    public $endDate;

    /**
     * @var waContact
     */
    public $contact;

    /**
     * @var bool
     */
    public $returnIterator;

    /**
     * @var int
     */
    public $start;

    /**
     * @var int
     */
    public $limit;

    /**
     * @var bool
     */
    public $reverse = false;

    /**
     * @var cashAggregateFilter
     */
    public $filter;

    /**
     * cashTransactionFilterParamsDto constructor.
     *
     * @param cashAggregateFilter    $filter
     * @param DateTimeImmutable|null $startDate
     * @param DateTimeImmutable|null $endDate
     * @param waContact|null         $contact
     * @param int|null               $start
     * @param int|null               $limit
     * @param bool                   $returnIterator
     * @param bool                   $reverse
     */
    public function __construct(
        cashAggregateFilter $filter,
        DateTimeImmutable $startDate = null,
        DateTimeImmutable $endDate = null,
        waContact $contact = null,
        int $start = null,
        int $limit = null,
        bool $returnIterator = true,
        bool $reverse = true
    ) {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->contact = $contact;
        $this->returnIterator = $returnIterator;
        $this->start = $start;
        $this->limit = $limit;
        $this->reverse = $reverse;
        $this->filter = $filter;
    }
}
