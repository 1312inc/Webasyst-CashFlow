<?php

final class cashDdsPluginHandlerListener
{
    /**
     * @return cashReportHandlerInterface[]
     */
    public function handle(cashReportHandlerParamsEvent $event): array
    {
        return [new cashDdsPluginHandler()];
    }
}
