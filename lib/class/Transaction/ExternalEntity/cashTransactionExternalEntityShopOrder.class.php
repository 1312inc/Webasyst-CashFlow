<?php

/**
 * Class cashTransactionExternalEntityShopOrder
 */
class cashTransactionExternalEntityShopOrder implements cashTransactionExternalEntityInterface
{
    const APP_ID = 'shop';

    /**
     * @var string
     */
    private $html = '';

    /**
     * cashTransactionExternalEntityShopOrder constructor.
     *
     * @param cashTransaction $transaction
     *
     * @throws waException
     */
    public function __construct(cashTransaction $transaction)
    {
        if (!wa()->appExists(self::APP_ID)) {
            return;
        }

        if ($transaction->getExternalSource() !== self::APP_ID) {
            return;
        }

        $data = $transaction->getExternalData();
        if (isset($data['id'])) {
            $orderId = $data['id'];
        } else {
            return;
        }

        $appName = _wd(self::APP_ID, wa(self::APP_ID)->getConfig()->getName());
        $appStatic = wa()->getAppStaticUrl(self::APP_ID);
        $appStaticAbsolute = wa()->getConfig()->getRootPath().$appStatic;
        $appUrl = wa()->getAppUrl(self::APP_ID);

        $appIcon = "{$appStatic}img/shop.png";
        $encodedOrder = shopHelper::encodeOrderId($orderId);
        $anchor = sprintf_wp('Order %s', $encodedOrder);
        $this->html = <<<HTML
<a href="{$appUrl}#/orders/id={$orderId}" target="_blank">
    <i class="icon16 pl-wa-app-icon" style="background-image: url({$appIcon}); background-size: 16px 16px;"></i>{$anchor}
</a>
HTML;
    }

    /**
     * @return string
     */
    public function getHtml()
    {
        return $this->html;
    }
}
