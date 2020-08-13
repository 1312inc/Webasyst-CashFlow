<?php

/**
 * Class cashReportDdsPeriod
 */
final class cashReportDdsPeriod
{
    const GROUPING_DAY   = 'day';
    const GROUPING_MONTH = 'month';
    const GROUPING_YEAR  = 'year';

    /**
     * @var string
     */
    private $name;

    /**
     * @var DateTime
     */
    private $start;

    /**
     * @var DateTime
     */
    private $end;

    /**
     * @var cashReportDdsPeriodGroupingDto[]
     */
    private $grouping = null;

    /**
     * @var string
     */
    private $groupBy;

    /**
     * cashReportDdsPeriod constructor.
     *
     * @param string   $name
     * @param DateTime $start
     * @param DateTime $end
     * @param          $groupBy
     */
    public function __construct($name, DateTime $start, DateTime $end, $groupBy)
    {
        $this->name = $name;
        $this->start = $start;
        $this->end = $end;
        $this->groupBy = $groupBy;
    }

    /**
     * @return array
     */
    public function getGrouping(): array
    {
        if ($this->grouping === null) {
            switch ($this->groupBy) {
                case self::GROUPING_MONTH:
                default:
                    $from = clone $this->start;
                    while ($from < $this->end) {
                        $this->grouping[$from->format('n')] = new cashReportDdsPeriodGroupingDto(
                            _w($from->format('F Y')),
                            $from->format('Y-m-d'),
                            $from->format('n')
                        );
                        cashDatetimeHelper::addMonthToDate($from);
                    }
            }
        }

        return $this->grouping;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStart(): DateTime
    {
        return $this->start;
    }

    public function getEnd(): DateTime
    {
        return $this->end;
    }

    public function getStartDate(): string
    {
        return $this->start->format('Y-m-d');
    }

    public function getEndDate(): string
    {
        return $this->end->format('Y-m-d');
    }

    /**
     * @param $key
     *
     * @return array|null
     */
    public function getGroupingByKey($key)
    {
        return $this->grouping[$key] ?? null;
    }

    /**
     * @param string $year
     *
     * @return cashReportDdsPeriod
     * @throws Exception
     */
    public static function createForYear($year): cashReportDdsPeriod
    {
        $start = DateTime::createFromFormat('Y-m-d|', date($year . '-01-01'));
        $end = clone $start;
        $end->modify('next year');

        return new self($year, $start, $end, self::GROUPING_MONTH);
    }
}
