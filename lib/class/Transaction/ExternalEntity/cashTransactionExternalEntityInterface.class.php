<?php

/**
 * Interface cashTransactionExternalEntityInterface
 */
interface cashTransactionExternalEntityInterface
{
    /**
     * @return string
     */
    public function getHtml(): string;

    /**
     * @return string
     */
    public function getIcon(): string;
}
