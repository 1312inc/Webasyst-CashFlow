<?php

final class cashFlowPluginHandlerListener
{
    /**
     * @return cashReportHandlerInterface[]
     */
    public function handle(cashReportHandlerParamsEvent $event): array
    {
        return [new cashFlowPluginHandler()];
    }
}
