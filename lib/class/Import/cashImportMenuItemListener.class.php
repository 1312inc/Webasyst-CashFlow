<?php

final class cashImportMenuItemListener
{
    /**
     * @return cashImportMenuItemInterface[]
     */
    public function handle(cashImportMenuItemEvent $event): array
    {
        return [
            new cashCsvImportMenuItem(),
            new cashShopImportMenuItem(),
        ];
    }
}
