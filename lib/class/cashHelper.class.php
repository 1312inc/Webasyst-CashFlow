<?php

/**
 * Class cashHelper
 */
final class cashHelper
{
    const AUTOMATION_LOG = 'automation';
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
                'compare_price' => '34 999', 'price' => '12 999 <span class="ruble">₽</span>/год',
                'upgrade_compare_price' => '34 999', 'upgrade_price' => '24 999 <span class="ruble">₽</span>/навсегда',
                'special' => ''
            );
            if (date('Ymd')<='20260430')
                $pricing = array(
                    'compare_price' => '34 999', 'price' => '13 999 <span class="ruble">₽</span>',
                    'upgrade_compare_price' => '24 999', 'upgrade_price' => '<span class="text-red">9 999 <span class="ruble">₽</span></span>',
                    'special' => '&minus;60% до 30.04', 'special_color' => 'red', 'special_button' => 'Предзаказ &minus;60% до 30.04'
                );
            elseif (date('Ymd')<='20260531')
                $pricing = array(
                    'compare_price' => '34 999', 'price' => '18 999 <span class="ruble">₽</span>',
                    'special' => '&minus;45% до 31.05', 'special_color' => 'red', 'special_button' => 'Большие деньги &minus;45% до 31.05'
                );
        }
        else
        {
            $pricing = array(
                'compare_price' => '$599', 'price' => '$219/yr',
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

    /**
     * @param $action_object waAPIMethod
     * @param $response array
     * @return array|null
     * @throws waException
     */
    public static function automationEvent($action_object, $response)
    {
        $map = [
            'cashTransactionCreateMethod' => 'create',
            'cashTransactionUpdateMethod' => 'update',
            'cashTransactionDeleteMethod' => 'delete'
        ];
        $class = get_class($action_object);
        $action = ifset($map, $class, null);
        if (!$action) {
            return null;
        }
        $action_id = 'transaction_'.$action;
        $automation_model = new cashAutomationModel();
        $rules = $automation_model->getByField('action_id', $action_id, true);

        $actions = cashAutomationAction::getActions();

        foreach ($rules as $rule) {
            $condition = ifset($rule, 'conditions', 0, []);
            $condition_id = ifset($condition, 'condition_id', '');
            $operator = ifset($condition, 'operator', '');
            $rule_data = ifset($rule, 'rule_data', []);
            $rule_action = ifset($rule_data, 'action', null);
            list($app, $plugin_id) = explode('.', $rule['app_id'].'.');
            $found = empty($condition);


            if ($plugin_id) {
                $params = [
                    'action_id'   => $action_id,
                    'condition'   => $condition,
                    'action'      => $rule_action,
                    'transaction' => $response
                ];

                $all_enabled_plugins = wa('cash')->getConfig()->getPlugins();
                if ($method = ifset($all_enabled_plugins, $plugin_id, 'handlers', cashEventStorage::WA_BACKEND_AUTOMATION_HANDLE, null)) {
                    try {
                        $found = wa()->getPlugin($plugin_id)->$method($params);
                    } catch (Exception $ex) {
                        cash()->getLogger()->error($ex->getMessage());
                    }
                }
            } else {
                switch ($condition_id) {
                    case 'amount':
                        break;
                    case 'description':
                        break;
                    case 'account_id':
                        break;
                    case 'category_id':
                        break;
                    case 'date':
                        break;
                    default:
                }
            }

            if ($found) {
                break;
            }
        }

        return [];
    }
}
