<?php

/**
 * Class cashHelper
 */
final class cashHelper
{
    const ACCESS_LIFE = 300;  //seconds

    /**
     * @param $access_code
     * @return bool|null
     * @throws waException
     */
    public static function checkAccessCode($access_code = null)
    {
        if (!self::isPremium()) {
            return null;
        }

        $app_settings_model = new waAppSettingsModel();
        $master_code = $app_settings_model->get('cash', 'access_code', null);
        if ($master_code === null) {
            return null;
        }

        if (!empty($access_code)) {
            if (password_verify($access_code, $master_code)) {
                $data = [
                    'access_life' => time(),
                    'ip' => waRequest::getIp(),
                    'user_agent' => waRequest::getUserAgent()
                ];
                wa()->getStorage()->set('master_access', $data);
                return true;
            }
            return false;
        }

        if ($master_access = wa()->getStorage()->get('master_access')) {
            if (
                (time() - ifempty($master_access, 'access_life', 0)) > self::ACCESS_LIFE
                || ifempty($master_access, 'ip', null) !== waRequest::getIp()
                || ifempty($master_access, 'user_agent', null) !== waRequest::getUserAgent()
            ) {
                wa()->getStorage()->remove('master_access');
                return false;
            }
            return true;
        }

        return false;
    }

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
        return cashLicensing::check(cashConfig::APP_ID)->isPremium();
    }

    /**
     * @return bool
     * @throws waException
     */
    public static function hasLicense()
    {
        return cashLicensing::check(cashConfig::APP_ID)->hasLicense();
    }

    /**
     * @return bool|string
     * @throws waException
     */
    public static function licenseExpirationDate()
    {
        return cashLicensing::check(cashConfig::APP_ID)->licenseExpiration();
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
              'upgrade' => '17 999 <span class="ruble">₽</span> / апгрейд навсегда'
            );
        }
        else
        {
            $pricing = array(
              'compare_price' => '$399',
              'price' => '$119/yr',
              'upgrade' => '$299 / lifetime upgrade'
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

    public static function sendEmail($email_to, $subject = '', $text = '')
    {
        $result = false;
        if (empty($subject)) {
            $subject = ifempty($subject, _w('Cash Flow alert'));
        }

        if ($email_to && $text) {
            try {
                $old_error_reporting = error_reporting();
                error_reporting($old_error_reporting & ~E_DEPRECATED);

                $message = new waMailMessage($subject, $text);
                $message->setFrom(wa()->getSetting('email', '', 'webasyst'));
                $message->setTo($email_to);
                $result = $message->send();
                if (!$result) {
                    cash()->getLogger()->log([_w('Email not sent'), $email_to, $subject, $text]);
                }
            } catch (Exception $ex) {
                cash()->getLogger()->log([_w('Email not sent'), $ex->getMessage(), $email_to, $subject, $text], 'error');
            } finally {
                error_reporting($old_error_reporting);
            }
        } else {
            cash()->getLogger()->log([_w('Email not sent'), _w('Email not sent: either recipient or email content are empty'), $subject]);
        }

        return $result;
    }
}
