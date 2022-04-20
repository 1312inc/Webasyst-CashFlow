<?php

final class cashReportSankeyDataGrouping
{
    /**
     * @var string
     */
    private $sqlGroupBy = '';

    /**
     * @var string
     */
    private $phpDateFormat = '';

    /**
     * @var bool
     */
    private $groupByMonth;

    public function __construct(DateTimeInterface $from, DateTimeInterface $to)
    {
        $this->groupByMonth = abs($from->diff($to)->days) > 50;

        if ($this->groupByMonth) {
            $this->sqlGroupBy = "DATE_FORMAT(ct.date, '%Y-%m')";
            $this->phpDateFormat = 'Y-m';
        } else {
            $this->sqlGroupBy = 'ct.date';
            $this->phpDateFormat = 'Y-m-d';
        }
    }

    public function getNextDate(DateTime $dateTime): DateTimeInterface
    {
        if ($this->groupByMonth) {
            cashDatetimeHelper::addMonthToDate($dateTime);
            return $dateTime;
        }

        return $dateTime->modify('+1 day');
    }

    public function getSqlGroupBy(): string
    {
        return $this->sqlGroupBy;
    }

    public function getPhpDateFormat(): string
    {
        return $this->phpDateFormat;
    }

    public function getFormattedDate(DateTimeInterface $dateTime): string
    {
        return $dateTime->format($this->phpDateFormat);
    }
}
