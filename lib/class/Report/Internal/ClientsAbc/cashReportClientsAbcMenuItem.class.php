<?php

final class cashReportClientsAbcMenuItem implements cashReportMenuItemInterface
{
    public function getIdentifier(): string
    {
        return 'clients-abc';
    }

    public function getIcon(): string
    {
        return '';
    }

    public function getName(): string
    {
        return _w('Clients ABC');
    }
}
