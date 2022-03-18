<?php

final class cashFlowPluginHandler implements cashReportHandlerInterface
{
    public function canHandle(string $identifier): bool
    {
        return $identifier === 'flow';
    }

    public function handle(array $params): string
    {
        return wa()->getView()->renderTemplate(
            wa()->getAppPath('plugins/flow/templates/Report.html'),
            [],
            true
        );
    }
}
