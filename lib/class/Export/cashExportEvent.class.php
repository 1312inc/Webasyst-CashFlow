<?php

/**
 * Class cashExportEvent
 *
 * @method cashTransactionPageFilterDto getObject()
 */
class cashExportEvent extends cashEvent
{
    /**
     * @var DateTime
     */
    private $startDate;

    /**
     * @var DateTime
     */
    private $endDate;

    /**
     * cashImportFileUploadedEvent constructor.
     *
     * @param cashTransactionPageFilterDto $object
     * @param DateTime                     $startDate
     * @param DateTime                     $endDate
     */
    public function __construct(cashTransactionPageFilterDto $object, DateTime $startDate, DateTime $endDate)
    {
        parent::__construct(cashEventStorage::WA_BACKEND_TRANSACTIONS_EXPORT, $object);
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * @return DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @return DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }
}
