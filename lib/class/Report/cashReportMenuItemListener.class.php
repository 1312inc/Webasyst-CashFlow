<?php

/**
 * Class cashReportMenuItemListener
 */
class cashReportMenuItemListener
{
    /**
     * @param cashReportMenuItemEvent $event
     *
     * @return cashReportMenuItemInterface[]
     */
    public function handle(cashReportMenuItemEvent $event): array
    {
        return [new cashReportDdsMenuItem(), new cashAccount()];
    }
}
