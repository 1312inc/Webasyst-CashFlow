<?php

final class cashReportPeriod
{
    public const GROUPING_DAY   = 'day';
    public const GROUPING_MONTH = 'month';
    public const GROUPING_YEAR  = 'year';

    /**
     * @var string
     */
    private $name;

    /**
     * @var DateTimeImmutable
     */
    private $start;

    /**
     * @var DateTimeImmutable
     */
    private $end;

    /**
     * @var cashReportPeriodGroupingDto[]
     */
    private $grouping = null;

    /**
     * @var string
     */
    private $groupBy;

    /**
     * @var string
     */
    private $value;

    public function __construct(
        string $name,
        string $value,
        DateTimeImmutable $start,
        DateTimeImmutable $end,
        string $groupBy
    ) {
        $this->name = $name;
        $this->start = $start;
        $this->end = $end;
        $this->groupBy = $groupBy;
        $this->value = $value;
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
                    $from = DateTime::createFromFormat('Y-m-d', $this->start->format('Y-m-d'));
                    while ($from < $this->end) {
                        $this->grouping[$from->format('n')] = new cashReportPeriodGroupingDto(
                            _w($from->format('M')),
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

    public function getStart(): DateTimeImmutable
    {
        return $this->start;
    }

    public function getEnd(): DateTimeImmutable
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

    public function getValue(): string
    {
        return $this->value;
    }

    public function getGroupingByKey(string $key)
    {
        return $this->grouping[$key] ?? null;
    }

    public static function createForYear(int $year): self
    {
        $start = DateTimeImmutable::createFromFormat('Y-m-d|', date($year . '-01-01'));
        $end = $start->modify('next year');

        return new self($year, $start->format('Y'), $start, $end, self::GROUPING_MONTH);
    }

    public function isEqual(cashReportPeriod $period): bool
    {
        return $this->value === $period->getValue()
            && $this->start === $period->getStart()
            && $this->end === $period->getEnd();
    }
}
