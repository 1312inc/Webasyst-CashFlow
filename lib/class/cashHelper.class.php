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
     * @param $contact_id
     * @return cashUser
     * @throws waException
     */
    public static function getContact($contact_id)
    {
        $repository = cash()->getEntityRepository(cashUser::class);

        return $repository->getUser($contact_id);
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
     * @throws waException
     */
    public static function hasLicense()
    {
        return waLicensing::check(cashConfig::APP_ID)->hasLicense();
    }

    /**
     * @return date (string) or null
     * @throws waException
     */
    public static function licenseExpirationDate()
    {
        if (!wa()->appExists('installer'))
            return null;

        wa('installer');
        $license = installerHelper::checkLicense('cash');

        if (empty($license['status'])) {
            // no license, no expiration date
            return null;
        } elseif (isset($license['expire_date'])) {
            // YYYY-MM-DD format
            return $license['expire_date'];
        } else {
            // lifetime license, no expireation date
            return null;
        }
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
        if (wa()->getLocale() == 'ru_RU')
        {
            $pricing = array(
              'compare_price' => '24 999',
              'price' => '7 199 <span class="ruble">₽</span>/год',
              'upgrade' => '16 999 <span class="ruble">₽</span> / апгрейд навсегда'
            );
        }
        else
        {
            $pricing = array(
              'compare_price' => '$399',
              'price' => '$119/yr',
              'upgrade' => '$289 / lifetime upgrade'
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
