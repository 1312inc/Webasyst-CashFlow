<?php

final class cashReportClientsAbcMenuItem implements cashReportMenuItemInterface
{
    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return 'clients-abc';
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return '';
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return _w('Clients');
    }
}
