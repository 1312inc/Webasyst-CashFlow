<?php

final class cashDdsPluginMenuItemListener
{
    /**
     * @return cashReportMenuItemInterface[]
     */
    public function handle(cashReportMenuItemEvent $event): array
    {
        return [new cashDdsPluginMenuItem()];
    }
}
