<?php

/**
 * Class cashGraphPeriodVO
 */
class cashGraphPeriodVO
{
    const NONE_PERIOD     = 'none';
    const ALL_TIME_PERIOD = 'all_time';
    const DAYS_PERIOD     = 'days';
    const MONTH_PERIOD    = 'months';
    const YEARS_PERIOD    = 'years';

    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $value;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $period;

    /**
     * @var string
     */
    private $id;

    /**
     * cashGraphPeriodVO constructor.
     *
     * @param string $type
     * @param int    $value
     */
    public function __construct($type, $value = null)
    {
        switch ($type) {
            case self::NONE_PERIOD:
                $this->name = _w('None');
                $value = 0;
                $this->period = self::DAYS_PERIOD;
                break;

            case self::ALL_TIME_PERIOD:
                $this->name = _w('All time');
                $value = -100;
                $this->period = self::YEARS_PERIOD;
                break;

            default:
                $last = '';
                if ($value < 0) {
                    $last = _w('Last ');
                }
                $this->period = $type;

                $this->name = $last.sprintf_wp('%d %s', abs($value), $type);
        }

        $this->value = $value;
        $this->id = $this->period.'|'.$this->value;
    }

    /**
     * @param DateTimeInterface|null $dateTime
     *
     * @return DateTime
     * @throws Exception
     */
    public function getDate(DateTimeInterface $dateTime = null)
    {
        if (!$dateTime instanceof DateTimeInterface) {
            $calculated = new DateTime();
        } else {
            $calculated = clone $dateTime;
        }

        return $calculated->modify(sprintf('%d %s', $this->value, $this->period));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
}
