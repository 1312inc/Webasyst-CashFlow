<?php

/**
 * Class cashTransactionFilterParamsDto
 */
class cashTransactionFilterParamsDto
{
    /**
     * @var int
     */
    public $accountId;

    /**
     * @var int
     */
    public $categoryId;

    /**
     * @var int
     */
    public $createContactId;

    /**
     * @var int
     */
    public $contractorContactId;

    /**
     * @var int
     */
    public $importId;

    /**
     * @var DateTime
     */
    public $startDate;

    /**
     * @var DateTime
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
     * cashTransactionFilterParamsDto constructor.
     *
     * @param int       $accountId
     * @param int       $categoryId
     * @param int       $createContactId
     * @param int       $contractorContactId
     * @param int       $importId
     * @param DateTime  $startDate
     * @param DateTime  $endDate
     * @param waContact $contact
     * @param int       $start
     * @param int       $limit
     * @param bool      $returnIterator
     * @param bool      $reverse
     */
    public function __construct(
        int $accountId = null,
        int $categoryId = null,
        int $createContactId = null,
        int $contractorContactId = null,
        int $importId = null,
        DateTime $startDate = null,
        DateTime $endDate = null,
        waContact $contact = null,
        int $start = null,
        int $limit = null,
        bool $returnIterator = true,
        bool $reverse = false
    ) {
        $this->accountId = $accountId;
        $this->categoryId = $categoryId;
        $this->createContactId = $createContactId;
        $this->contractorContactId = $contractorContactId;
        $this->importId = $importId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->contact = $contact;
        $this->returnIterator = $returnIterator;
        $this->start = $start;
        $this->limit = $limit;
        $this->reverse = $reverse;
    }
}
