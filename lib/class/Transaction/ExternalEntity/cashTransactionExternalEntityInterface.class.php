<?php

/**
 * Interface cashTransactionExternalEntityInterface
 */
interface cashTransactionExternalEntityInterface
{
    /**
     * cashTransactionExternalEntityInterface constructor.
     *
     * @param cashTransaction $transaction
     */
    public function __construct(cashTransaction $transaction);

    /**
     * @return string
     */
    public function getHtml();
}
