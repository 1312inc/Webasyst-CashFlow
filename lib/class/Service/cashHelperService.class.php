<?php


class cashHelperService
{
    const TIME_TOO_LATE  = 0;
    const TIME_TOO_EARLY = -1;
    const TIME_OK        = 1;
    const STOP_TIME = '19:50';


    public function getCallBounds($dayOfWeek = false)
    {
        $bounds = [
            0 => [
                'start' => '09:00',
                'end'   => '19:45',
            ],
            1 => [
                'start' => '08:00',
                'end'   => '19:45',
            ],
            2 => [
                'start' => '08:00',
                'end'   => '19:45',
            ],
            3 => [
                'start' => '09:00',
                'end'   => '19:45',
            ],
            4 => [
                'start' => '08:00',
                'end'   => '19:45',
            ],
            5 => [
                'start' => '08:00',
                'end'   => '19:45',
            ],
            6 => [
                'start' => '09:00',
                'end'   => '19:45',
            ],
        ];

        if ($dayOfWeek && isset($bounds[$dayOfWeek])) {
            return $bounds[$dayOfWeek];
        }

        return $bounds;
    }

    public function isValidTime($timezoneValue = 0, \DateTime $serverTime = null)
    {
        $bounds = $this->getCallBounds();

        $serverTime = $serverTime ?: new \DateTime();
        $borrowerTime = clone $serverTime;
        $dayOfWeek = $serverTime->format('w');

        if (!isset($bounds[$dayOfWeek])) {
            return self::TIME_TOO_LATE;
        }
        $boundTimes = $bounds[$dayOfWeek];
        $stopTime = self::STOP_TIME;

        $date = clone $serverTime;
        switch ($date->format('Y-m-d')) {
            case '2019-12-31':
                $stopTime = '16:45';
                break;

            case '2020-01-01':
            case '2020-01-02':
            case '2020-01-07':
                $stopTime = '00:00';
                break;

            case '2020-01-03':
            case '2020-01-04':
            case '2020-01-05':
            case '2020-01-06':
            case '2020-01-08':
                $boundTimes['start'] = '09:00';
                break;
        }

        if ($serverTime->format('H:i') > $stopTime) {
            return self::TIME_TOO_LATE;
        }

        $timezoneHours = (int)$timezoneValue;

        if ($timezoneHours) {
            $borrowerTime->modify($timezoneHours.' hours');
        }

        if ($borrowerTime->format('w') !== $dayOfWeek) {
            return self::TIME_TOO_LATE;
        }

        if ($serverTime->format('Y-m-d') < $borrowerTime->format('Y-m-d')) {
            return self::TIME_TOO_LATE;
        }

        if ($serverTime->format('Y-m-d') > $borrowerTime->format('Y-m-d')) {
            return self::TIME_TOO_EARLY;
        }

        if ($borrowerTime->format('H:i') < $boundTimes['start']) {
            return self::TIME_TOO_EARLY;
        }

        if ($borrowerTime->format('H:i') > $boundTimes['end']) {
            return self::TIME_TOO_LATE;
        }

        return self::TIME_OK;
    }
}