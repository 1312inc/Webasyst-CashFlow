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
            $pricing = array( 'compare_price' => '27 999', 'price' => '11 999 <span class="ruble">₽</span>', 'special' => '' );
            if (date('Ymd')<='20251031') $pricing = array( 'compare_price' => '11 999', 'price' => '8 999 <span class="ruble">₽</span>', 'special' => '&minus;25% до 31.10', 'special_short' => '&minus;25% / 31.10', 'special_color' => 'orange' );
        }
        else
        {
            $pricing = array( 'compare_price' => '$449', 'price' => '$199', 'special' => '' );
            if (date('Ymd')<='20251031') $pricing = array( 'compare_price' => '$199', 'price' => '$149', 'special' => '&minus;25%', 'special_short' => '&minus;25% / 10.31', 'special_color' => 'orange' );
        }

        return $pricing;
    }
}
