<?php

final class cashReportInternalMenuItemListener
{
    /**
     * @return cashReportMenuItemInterface[]
     */
    public function handle(cashReportMenuItemEvent $event): array
    {
        return [
            new cashReportDdsMenuItem(),
            new cashReportSankeyMenuItem(),
            new cashReportDdsCategoriesMenuItem(),
            new cashReportStreamMenuItem(),
            new cashReportClientsAbcMenuItem(),
        ];
    }
}
