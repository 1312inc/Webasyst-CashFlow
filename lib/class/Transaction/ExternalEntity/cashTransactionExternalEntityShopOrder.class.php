<?php

/**
 * Class cashTransactionExternalEntityShopOrder
 */
class cashTransactionExternalEntityShopOrder implements cashTransactionExternalEntityInterface
{
    private const APP_ID = 'shop';

    /**
     * @var string
     */
    private $html = '';

    /**
     * @var string
     */
    private $icon = '';

    /**
     * cashTransactionExternalEntityShopOrder constructor.
     *
     * @param string $app
     * @param mixed  $data
     *
     * @throws waException
     */
    public function __construct(string $app, $data)
    {
        if ($app !== self::APP_ID) {
            return;
        }

        if (!wa()->appExists(self::APP_ID)) {
            return;
        }

        if (!is_array($data)) {
            $data = json_decode($data, true);
        }
        $appName = _wd(self::APP_ID, wa(self::APP_ID)->getConfig()->getName());
        $appStatic = wa()->getAppStaticUrl(self::APP_ID);
//        $appStaticAbsolute = wa()->getConfig()->getRootPath().$appStatic;
        $appUrl = wa()->getAppUrl(self::APP_ID);
        $appIcon = "{$appStatic}img/shop.png";
        $this->icon = <<<HTML
<i class="icon16 pl-wa-app-icon" style="background-image: url({$appIcon}); background-size: 16px 16px;" title="{$appName}"></i>
HTML;

        if (isset($data['id'])) {
            $orderId = $data['id'];
            $encodedOrder = shopHelper::encodeOrderId($orderId);
            $anchor = sprintf_wp('Order %s', $encodedOrder);
            $this->icon = <<<HTML
<i class="icon16 pl-wa-app-icon" style="background-image: url({$appIcon}); background-size: 16px 16px;" title="{$appName}"></i>
HTML;

            $this->html = <<<HTML
<a href="{$appUrl}#/orders/id={$orderId}" target="_blank">
    {$this->icon}{$anchor}
</a>
HTML;
        }
    }

    /**
     * @return string
     */
    public function getHtml(): string
    {
        return $this->html;
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icon;
    }
}
