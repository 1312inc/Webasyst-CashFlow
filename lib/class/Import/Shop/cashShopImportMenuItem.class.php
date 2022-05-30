<?php

final class cashShopImportMenuItem implements cashImportMenuItemInterface
{
    public function getIdentifier(): string
    {
        return 'shop/settings';
    }

    public function getAnchor(): string
    {
        $waUrl = wa()->getRootUrl();

        return sprintf('<i class="icon" style="background-image: url(%swa-apps/shop/img/shop.png);"></i> Shop-Script', $waUrl);
    }

    public function getUrl(): ?string
    {
        return 'shop/settings';
    }
}
