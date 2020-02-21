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

}