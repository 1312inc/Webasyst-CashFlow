<?php

/**
 * Class cashDatetimeHelper
 */
class cashDatetimeHelper
{
    const DEFAULT_DATEFORMAT = 'ai';

    /**
     * @param string $datetime
     * @param string $format
     *
     * @return DateTime
     * @throws Exception
     */
    public static function createDateTimeFromFormat($datetime, $format)
    {
        $formats = self::getDatetimeFormats();

        try {
            if (isset($formats[$format])) {
                return DateTime::createFromFormat($formats[$format], $datetime);
            }

            if ($format !== self::DEFAULT_DATEFORMAT) {
                return DateTime::createFromFormat($format, $datetime);
            }
        } catch (Exception $ex) {
        }

        return new DateTime($datetime);
    }

    /**
     * @return array
     */
    public static function getDatetimeFormats()
    {
        return [
            'dd.mm.yyyy' => 'd.m.Y',
            'dd/mm/yyyy' => 'd/m/Y',
            'mm.dd.yyyy' => 'm.d.Y',
            'mm/dd/yyyy' => 'm/d/Y',
            'yyyy-mm-dd' => 'Y-m-d',
        ];
    }

    /**
     * @param DateTime      $dateTime
     * @param int           $monthCount
     * @param DateTime|null $relativeDate
     */
    public static function addMonthToDate(DateTime $dateTime, $monthCount = 1, DateTime $relativeDate = null)
    {
        $nextDate = clone $dateTime;
        $dayOfMonth = $relativeDate ? $relativeDate->format('j') : $dateTime->format('j');
        if ($dayOfMonth > 28 && $nextDate->modify("last day of {$monthCount} month")->format('j') < $dayOfMonth) {
            $dateTime->modify("last day of {$monthCount} month");
        } else {
            $dateTime->modify("+{$monthCount} month");
            $dateTime->setDate($dateTime->format('Y'), $dateTime->format('n'), $dayOfMonth);
        }
    }
}
