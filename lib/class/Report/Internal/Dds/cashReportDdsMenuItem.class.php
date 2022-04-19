<?php

final class cashReportDdsMenuItem implements cashReportMenuItemInterface
{
    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return 'dds';
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
        return _w('Cash flow report');
    }
}
