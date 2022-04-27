<?php

/**
 * Interface cashReportMenuItemInterface
 */
interface cashReportMenuItemInterface
{
    /**
     * @return string
     */
    public function getIdentifier(): string;

    /**
     * @return string
     */
    public function getIcon(): string;

    /**
     * @return string
     */
    public function getName(): string;
}
