<?php

final class cashReportFlowHandler implements cashReportHandlerInterface
{
    public function canHandle(string $identifier): bool
    {
        return $identifier === 'flow';
    }

    public function handle(array $params): string
    {
        return wa()->getView()->renderTemplate(
            wa()->getAppPath('templates/actions/report/internal/ReportFlow.html'),
            [],
            true
        );
    }
}
