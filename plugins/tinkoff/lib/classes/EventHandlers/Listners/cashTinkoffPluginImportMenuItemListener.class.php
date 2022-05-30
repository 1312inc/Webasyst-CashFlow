<?php

final class cashTinkoffPluginImportMenuItemListener
{
    /**
     * @return cashImportMenuItemInterface[]
     */
    public function handle(cashImportMenuItemEvent $event): array
    {
        return [new cashTinkoffPluginImportMenuItem()];
    }
}
