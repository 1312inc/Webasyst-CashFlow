<?php

/**
 * Class cashReportDdsPeriod
 */
final class cashReportDdsPeriod
{
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
     * cashReportDdsPeriod constructor.
     *
     * @param string   $name
     * @param DateTime $start
     * @param DateTime $end
     */
    public function __construct($name, DateTime $start, DateTime $end)
    {
        $this->name = $name;
        $this->start = $start;
        $this->end = $end;
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
     * @param string $year
     *
     * @return cashReportDdsPeriod
     * @throws Exception
     */
    public static function createForYear($year): cashReportDdsPeriod
    {
        $start = new DateTime($year . '-m-d');
        $end = clone $start;
        $end->modify('next year');

        return new self($year, $start, $end);
    }
}
