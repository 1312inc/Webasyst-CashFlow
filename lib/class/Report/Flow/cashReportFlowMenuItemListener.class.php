<?php

final class cashReportFlowMenuItemListener
{
    /**
     * @return cashReportMenuItemInterface[]
     */
    public function handle(cashReportMenuItemEvent $event): array
    {
        return [new cashReportFlowMenuItem(), new cashAccount()];
    }
}
