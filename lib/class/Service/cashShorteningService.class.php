<?php

/**
 * Class cashShorteningService
 */
class cashShorteningService
{
    /**
     * @param float $amount
     *
     * @return string
     */
    public static function money($amount)
    {
        $sign = '';
        if ($amount < 0) {
            $sign = '&minus;';
        }

        $shorten = abs((float)$amount);
        if ($shorten >= 10000000) {
            $shorten = sprintf('%.1fM', $shorten / 1000000);
        } elseif ($shorten >= 1000000) {
            $shorten = sprintf('%.2fM', $shorten / 1000000);
        } elseif ($shorten >= 100000) {
            $shorten = sprintf('%dK', $shorten / 1000);
        } elseif ($shorten >= 1000) {
            $shorten = sprintf('%.1fK', $shorten / 1000);
        } elseif ($shorten >= 100) {
            $shorten = (string)round($shorten, 0);
        } elseif ($shorten >= 10) {
            $shorten = (string)round($shorten, 1);
        } else {
            $shorten = (string)round($shorten, 2);
        }

        return $sign.self::removeTrailingZeros($shorten);
    }

    /**
     * @param float $num
     *
     * @return string
     */
    private static function removeTrailingZeros($num)
    {
        $pos = strpos($num, '.');
        if ($pos === false) {
            return $num;
        }

        return preg_replace('/(\d+)(\.0+)([MK])/s', '$1$3', $num);
    }
}
