<?php

final class cashApiTransactionGetTodayCountDto
{
    /**
     * @var DateTimeImmutable
     */
    public $date;

    /**
     * @var int
     */
    public $onBadge = 0;

    /**
     * @var int
     */
    public $countOnDate = 0;

    /**
     * cashApiTransactionTodayCountDto constructor.
     *
     * @param DateTimeImmutable $date
     * @param int               $onBadge
     * @param int               $countOnDate
     */
    public function __construct(DateTimeImmutable $date, int $onBadge, int $countOnDate)
    {
        $this->date = $date;
        $this->onBadge = $onBadge;
        $this->countOnDate = $countOnDate;
    }
}
