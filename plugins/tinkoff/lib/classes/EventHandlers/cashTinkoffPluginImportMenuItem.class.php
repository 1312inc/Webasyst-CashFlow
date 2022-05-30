<?php

final class cashTinkoffPluginImportMenuItem implements cashImportMenuItemInterface
{
    public function getIdentifier(): string
    {
        return cashTinkoffPluginImportHandler::IDENTIFIER;
    }

    public function getAnchor(): string
    {
        $waUrl = wa()->getRootUrl();

        return sprintf('<i class="icon" style="background-image: url(%swa-apps/shop/img/shop.png);"></i> Tinkoff', $waUrl);
    }

    public function getUrl(): ?string
    {
        return null;
    }
}
