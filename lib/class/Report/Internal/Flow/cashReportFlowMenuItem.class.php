<?php

final class cashReportFlowMenuItem implements cashReportMenuItemInterface
{
    public function getIdentifier(): string
    {
        return 'flow';
    }

    public function getIcon(): string
    {
        return '';
    }

    public function getName(): string
    {
        return _w('FLOW');
    }
}
