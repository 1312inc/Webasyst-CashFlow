<?php

final class cashApiTransactionGetBadgeCountDto
{
    /**
     * @var DateTimeImmutable
     */
    public $date;

    /**
     * @var int
     */
    public $count = 0;

    /**
     * cashApiTransactionBadgeCountDto constructor.
     *
     * @param DateTimeImmutable $date
     * @param int               $count
     */
    public function __construct(DateTimeImmutable $date, int $count)
    {
        $this->date = $date;
        $this->count = $count;
    }
}
