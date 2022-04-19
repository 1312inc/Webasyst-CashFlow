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
            new cashReportDdsCategoriesMenuItem(),
            new cashReportSankeyMenuItem(),
            new cashReportClientsAbcMenuItem(),
            new cashReportStreamMenuItem(),
        ];
    }
}
