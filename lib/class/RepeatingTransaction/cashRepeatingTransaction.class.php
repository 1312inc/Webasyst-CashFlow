<?php

/**
 * Class cashRepeatingTransaction
 */
class cashRepeatingTransaction extends cashTransaction
{
    use kmwaEntityDatetimeTrait;
    use cashEntityJsonTransformerTrait;

    const DEFAULT_REPEATING_FREQUENCY = 1;

    const INTERVAL_NONE   = 'none';
    const INTERVAL_DAY   = 'day';
    const INTERVAL_WEEK  = 'week';
    const INTERVAL_MONTH = 'month';
    const INTERVAL_YEAR  = 'year';

    const REPEATING_END_NEVER  = 'never';
    const REPEATING_END_AFTER  = 'after';
    const REPEATING_END_ONDATE = 'ondate';

    /**
     * @var int
     */
    private $enabled = 1;

    /**
     * @var int
     */
    private $repeating_interval = self::INTERVAL_NONE;

    /**
     * @var string
     */
    private $repeating_frequency = self::DEFAULT_REPEATING_FREQUENCY;

    /**
     * @var array|string
     */
    private $repeating_conditions = [];

    /**
     * @var string
     */
    private $repeating_end_type = self::REPEATING_END_NEVER;

    /**
     * @var array|string
     */
    private $repeating_end_conditions = [];

    /**
     * @var array|string
     */
    private $transfer = [];

    /**
     * @var int
     */
    private $repeating_occurrences = 0;

    public function beforeExtract(array &$fields)
    {
        $this->toJson(['repeating_conditions', 'repeating_end_conditions', 'transfer']);
    }

    public function afterExtract(array &$fields)
    {
        $this->fromJson(['repeating_conditions', 'repeating_end_conditions', 'transfer']);
    }

    public function afterHydrate($data = [])
    {
        $this->fromJson(['repeating_conditions', 'repeating_end_conditions', 'transfer']);
    }

    /**
     * @return int
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param int $enabled
     *
     * @return cashRepeatingTransaction
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return int
     */
    public function getRepeatingInterval()
    {
        return $this->repeating_interval;
    }

    /**
     * @param int $repeating_interval
     *
     * @return cashRepeatingTransaction
     */
    public function setRepeatingInterval($repeating_interval)
    {
        $this->repeating_interval = $repeating_interval;

        return $this;
    }

    /**
     * @return string
     */
    public function getRepeatingFrequency()
    {
        return $this->repeating_frequency;
    }

    /**
     * @param string $repeating_frequency
     *
     * @return cashRepeatingTransaction
     */
    public function setRepeatingFrequency($repeating_frequency)
    {
        $this->repeating_frequency = $repeating_frequency;

        return $this;
    }

    /**
     * @return array|string
     */
    public function getRepeatingConditions()
    {
        return $this->repeating_conditions;
    }

    /**
     * @param array|string $repeating_conditions
     *
     * @return cashRepeatingTransaction
     */
    public function setRepeatingConditions($repeating_conditions)
    {
        $this->repeating_conditions = $repeating_conditions;

        return $this;
    }

    /**
     * @return array|string
     */
    public function getRepeatingEndConditions()
    {
        return $this->repeating_end_conditions;
    }

    /**
     * @param array|string $repeating_end_conditions
     *
     * @return cashRepeatingTransaction
     */
    public function setRepeatingEndConditions($repeating_end_conditions)
    {
        $this->repeating_end_conditions = $repeating_end_conditions;

        return $this;
    }

    /**
     * @return int
     */
    public function getRepeatingOccurrences()
    {
        return $this->repeating_occurrences;
    }

    /**
     * @param int $repeating_occurrences
     *
     * @return cashRepeatingTransaction
     */
    public function setRepeatingOccurrences($repeating_occurrences)
    {
        $this->repeating_occurrences = $repeating_occurrences;

        return $this;
    }

    /**
     * @return array
     */
    public static function getRepeatingIntervals()
    {
        return [
            self::INTERVAL_DAY => _w('day'),
            self::INTERVAL_WEEK => _w('week'),
            self::INTERVAL_MONTH => _w('month'),
            self::INTERVAL_YEAR => _w('year'),
        ];
    }

    /**
     * @return array
     */
    public static function getRepeatingEveryIntervals()
    {
        return [
            self::INTERVAL_DAY => _w('Every day'),
            self::INTERVAL_WEEK => _w('Every week'),
            self::INTERVAL_MONTH => _w('Every month'),
            self::INTERVAL_YEAR => _w('Every year'),
        ];
    }

    /**
     * @return array
     */
    public static function getRepeatingEndTypes()
    {
        return [
            self::REPEATING_END_NEVER => _w('Never'),
            self::REPEATING_END_AFTER => _w('After'),
            self::REPEATING_END_ONDATE => _w('On date'),
        ];
    }

    /**
     * @return string
     */
    public function getRepeatingEndType()
    {
        return $this->repeating_end_type;
    }

    /**
     * @param string $repeating_end_type
     *
     * @return cashRepeatingTransaction
     */
    public function setRepeatingEndType($repeating_end_type)
    {
        $this->repeating_end_type = $repeating_end_type;

        return $this;
    }

    /**
     * @return array|string
     */
    public function getTransfer()
    {
        return $this->transfer;
    }

    /**
     * @param array $transfer
     *
     * @return cashRepeatingTransaction
     */
    public function setTransfer($transfer = null)
    {
        $this->transfer = $transfer;

        return $this;
    }
}
