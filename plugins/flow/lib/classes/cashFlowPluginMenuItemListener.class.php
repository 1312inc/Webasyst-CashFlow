<?php

final class cashFlowPluginMenuItemListener
{
    /**
     * @return cashReportMenuItemInterface[]
     */
    public function handle(cashReportMenuItemEvent $event): array
    {
        return [new cashFlowPluginMenuItem()];
    }
}
