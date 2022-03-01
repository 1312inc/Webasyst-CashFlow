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
     * @var string|null
     */
    private $appUrl;

    /**
     * @var int|null
     */
    private $orderId;

    /**
     * @var string|null
     */
    private $appIcon;

    /**
     * @var string|null
     */
    private $appName;

    /**
     * @var string|null
     */
    private $entityName;

    /**
     * @var bool
     */
    private $isSelfDestructWhenDue;

    /**
     * cashTransactionExternalEntityShopOrder constructor.
     *
     * @param string $app
     * @param mixed  $data
     *
     * @throws waException
     */
    public function __construct(string $app, $data, ?string $hash = null)
    {
        if ($app !== self::APP_ID) {
            return;
        }

        if (!wa()->appExists(self::APP_ID)) {
            return;
        }

        $this->isSelfDestructWhenDue = ($hash === cashShopTransactionFactory::HASH_FORECAST);

        if (!is_array($data)) {
            $data = json_decode($data, true);
        }
        $this->appName = _wd(self::APP_ID, wa(self::APP_ID)->getConfig()->getName());
        $appStatic = wa()->getAppStaticUrl(self::APP_ID);
//        $appStaticAbsolute = wa()->getConfig()->getRootPath().$appStatic;
        $this->appUrl = wa()->getAppUrl(self::APP_ID);
        $this->appIcon = "{$appStatic}img/shop.png";
        $this->icon = <<<HTML
<i class="icon16 pl-wa-app-icon" style="background-image: url({$this->appIcon}); background-size: 16px 16px;" title="{$this->appName}"></i>
HTML;

        if (isset($data['id'])) {
            $this->orderId = (int) $data['id'];
            $encodedOrder = shopHelper::encodeOrderId($this->orderId);
            $this->entityName = sprintf_wp('Order %s', $encodedOrder);
            $this->icon = <<<HTML
<i class="icon16 pl-wa-app-icon" style="background-image: url({$this->appIcon}); background-size: 16px 16px;" title="{$this->appName}"></i>
HTML;

            $this->html = <<<HTML
<a href="{$this->getLink()}" target="_blank">
    {$this->icon}{$this->entityName}
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

    public function getLink(): string
    {
        return "{$this->appUrl}#/orders/id={$this->orderId}";
    }

    public function getAppUrl(): ?string
    {
        return $this->appUrl;
    }

    public function getId(): ?int
    {
        return $this->orderId;
    }

    public function getAppIcon(): ?string
    {
        return $this->appIcon;
    }

    public function getAppName(): ?string
    {
        return $this->appName;
    }

    public function getEntityName(): ?string
    {
        return $this->entityName;
    }

    public function isSelfDestructWhenDue(): bool
    {
        return $this->isSelfDestructWhenDue;
    }
}
