<?php

final class cashExternalShopInfoGetter implements cashExternalSourceInfoGetterInterface
{
    private const APP = 'shop';

    public function info(string $id): ?cashExternalInfoDto
    {
        if (!wa()->appExists(self::APP)) {
            return null;
        }

        wa(self::APP);

        // Normal format?
        if (wa_is_int($id)) {
            $collection = new shopOrdersCollection('id/'.$id);
            $orders = $collection->getOrders('id', 0, 1);
        }
        if (empty($orders)) {
            // frontend format
            $collection = new shopOrdersCollection('search/id='.$id);
            $orders = $collection->getOrders('id', 0, 1);
        }
        if (!$orders) {
            return null;
        }
        $id = (int) reset($orders)['id'];

        $info = wa()->getAppInfo(self::APP);
        $rootUrl = rtrim(wa()->getRootUrl(true), '/');

        return new cashExternalInfoDto(
            self::APP,
            $info['name'],
            sprintf('%s/%s', $rootUrl, $info['img']),
            (int) $id,
            sprintf_wp('Order %s', shopHelper::encodeOrderId((int) $id)),
            sprintf('%s/%s', $rootUrl, $info['img']),
            sprintf(
                '%s%s%s/?action=orders#/order/%d/',
                $rootUrl,
                wa()->getConfig()->getBackendUrl(true),
                self::APP,
                $id
            ),
            []
        );
    }
}
