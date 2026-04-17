<?php

/**
 * Class cashHelper
 */
final class cashHelper
{
    /**
     * @return array
     * @throws waException
     */
    public static function getAllStorefronts()
    {
        $storefronts = [];
        $idna = new waIdna();
        $routing = new waRouting(wa());
        foreach ($routing->getByApp('shop') as $domain => $domain_routes) {
            foreach ($domain_routes as $route) {
                $url = rtrim($domain . '/' . $route['url'], '/*');
                if (strpos($url, '/') !== false) {
                    $url .= '/';
                }
                $storefronts[] = [
                    'domain' => $domain,
                    'route' => $route,
                    'url' => $url,
                    'url_decoded' => $idna->decode($url),
                ];
            }
        }

        return $storefronts;
    }

    /**
     * @param $value
     *
     * @return float
     */
    public static function parseFloat($value): float
    {
        return (float)str_replace(',','.',trim($value));
    }

    /**
     * @return bool
     * @throws waException
     */
    public static function isPremium()
    {
        return waLicensing::check(cashConfig::APP_ID)->isPremium();
    }

    /**
     * @return bool
     */
    public static function isCloud()
    {
        return wa()->appExists('hosting');
    }

    /**
     * @return array
     */
    public static function getPremiumPricing()
    {
        // vofka says sorry for such a hard code
        // we were young and needed the money

        if (wa()->getLocale() == 'ru_RU')
        {
            $pricing = array(
                'compare_price' => '', 'price' => '34 999 <span class="ruble">₽</span>',
                'upgrade_compare_price' => '34 999', 'upgrade_price' => '24 999 <span class="ruble">₽</span>',
                'special' => ''
            );
            if (date('Ymd')<='20260430')
                $pricing = array(
                    'compare_price' => '34 999', 'price' => '13 999 <span class="ruble">₽</span>',
                    'upgrade_compare_price' => '24 999', 'upgrade_price' => '9 999 <span class="ruble">₽</span>',
                    'special' => '&minus;60% до 30.04', 'special_color' => 'green', 'special_button' => 'Предзаказ &minus;60% до 30.04'
                );
            elseif (date('Ymd')<='20260531')
                $pricing = array(
                    'compare_price' => '34 999', 'price' => '19 999 <span class="ruble">₽</span>',
                    'upgrade_compare_price' => '24 999', 'upgrade_price' => '14 999 <span class="ruble">₽</span>',
                    'special' => '&minus;40% до 31.05', 'special_color' => 'red', 'special_button' => 'Большие деньги &minus;40% до 31.05'
                );
        }
        else
        {
            $pricing = array(
                'compare_price' => '', 'price' => '$599',
                'upgrade_compare_price' => '$599', 'upgrade_price' => '$399',
                'special' => ''
            );
        }

        return $pricing;
    }

    /**
     * @param $date
     * @param $tz
     * @return string|null
     */
    public static function convertDateToISO8601($date, $tz = 'UTC')
    {
        if (empty($date)) {
            return null;
        }
        try {
            $dt = new DateTime((string) $date);
            if ($tz) {
                $dt->setTimezone(new DateTimeZone($tz));
            }
        } catch (Exception $ex) {
            return $date;
        }

        return $dt->format('Y-m-d\TH:i:s.u\Z');
    }
}
