<?php

final class cashReportSankeyMenuItem implements cashReportMenuItemInterface
{
    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return 'sankey';
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
        return _w('Sankey');
    }
}
