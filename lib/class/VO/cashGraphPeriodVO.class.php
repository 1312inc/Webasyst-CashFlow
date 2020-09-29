<?php

/**
 * Class cashGraphPeriodVO
 */
class cashGraphPeriodVO implements JsonSerializable
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

            case self::DAYS_PERIOD:
                $typeStr = _w('day', 'days', abs($value));
                if ($value < 0) {
                    $this->name = sprintf_wp('Last %d %s', abs($value), $typeStr);
                } else {
                    $this->name = sprintf_wp('%d %s', abs($value), $typeStr);
                }
                $this->period = $type;
                break;

            case self::MONTH_PERIOD:
                $typeStr = _w('month', 'months', abs($value));
                if ($value < 0) {
                    $this->name = sprintf_wp('Last %d %s', abs($value), $typeStr);
                } else {
                    $this->name = sprintf_wp('%d %s', abs($value), $typeStr);
                }
                $this->period = $type;
                break;

            case self::YEARS_PERIOD:
                $typeStr = _w('year', 'years', abs($value));
                if ($value < 0) {
                    $this->name = sprintf_wp('Last %d %s', abs($value), $typeStr);
                } else {
                    $this->name = sprintf_wp('%d %s', abs($value), $typeStr);
                }
                $this->period = $type;
                break;

            default:
                throw new kmwaRuntimeException(sprintf('Unknown graph period type %s', $type));
        }

        $this->value = $value;
        $this->id = $this->period.'|'.$this->value;
        $this->type = $type;
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
     * @param DateTimeInterface|null $dateTime
     *
     * @return string
     * @throws Exception
     */
    public function getDateAsString(DateTimeInterface $dateTime = null)
    {
        return $this->getDate()->format('Y-m-d H:i:s');
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

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'type' => $this->type,
            'value' => $this->value,
        ];
    }
}
