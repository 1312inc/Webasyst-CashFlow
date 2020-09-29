<?php

/**
 * Class cashTransactionExternalEntityFactory
 */
class cashTransactionExternalEntityFactory
{
    /**
     * @param cashTransaction $transaction
     *
     * @return cashTransactionExternalEntityInterface
     * @throws waException
     */
    public static function createFromTransaction(cashTransaction $transaction)
    {
        return self::createFromSource($transaction->getExternalSource(), $transaction->getExternalData());
    }

    /**
     * @param string $source
     * @param mixed  $data
     *
     * @return cashTransactionExternalEntityInterface
     * @throws waException
     */
    public static function createFromSource($source, $data)
    {
        if ($source === 'shop') {
            return new cashTransactionExternalEntityShopOrder($source, $data);
        }

        return null;
    }
}
