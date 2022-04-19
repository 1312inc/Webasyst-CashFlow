<?php

final class cashReportInternalHandlerListener
{
    /**
     * @return cashReportHandlerInterface[]
     */
    public function handle(cashReportHandlerParamsEvent $event): array
    {
        return [
            new cashReportDdsHandler(),
            new cashReportDdsCategoriesHandler(),
            new cashReportSankeyHandler(),
            new cashReportClientsAbcHandler(),
            new cashReportStreamHandler(),
        ];
    }
}
