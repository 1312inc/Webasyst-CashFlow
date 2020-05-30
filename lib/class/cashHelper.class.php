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
    public static function parseFloat($value)
    {
        return (float)str_replace(',','.',trim($value));
    }
}
