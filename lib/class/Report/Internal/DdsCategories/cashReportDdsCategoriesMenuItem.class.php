<?php

final class cashReportDdsCategoriesMenuItem implements cashReportMenuItemInterface
{
    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return 'categories';
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
        return _w('Categories');
    }
}
