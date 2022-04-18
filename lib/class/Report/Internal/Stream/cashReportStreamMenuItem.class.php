<?php

final class cashReportStreamMenuItem implements cashReportMenuItemInterface
{
    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return 'stream';
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
        return _w('Stream');
    }
}
