<?php

class cashExternalShopInfoGetter implements cashExternalSourceInfoGetterInterface
{
    private const APP = 'shop';

    public function info(string $id): ?cashExternalInfoDto
    {
        if (!wa()->appExists(self::APP)) {
            return null;
        }

        wa(self::APP);
        try {
            $order = new shopOrder((int) $id);
        } catch (waException $e) {
            return null;
        }

        $info = wa()->getAppInfo(self::APP);
        $rootUrl = rtrim(wa()->getRootUrl(true), '/');

        return new cashExternalInfoDto(
            self::APP,
            $info['name'],
            sprintf('%s/%s', $rootUrl, $info['img']),
            (int) $id,
            shopHelper::encodeOrderId((int) $id),
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
