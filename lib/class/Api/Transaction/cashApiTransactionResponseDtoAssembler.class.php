<?php

/**
 * Class cashApiTransactionResponseDtoAssembler
 */
class cashApiTransactionResponseDtoAssembler
{
    /**
     * @param Iterator $transactionData
     *
     * @return Generator
     */
    public static function generateResponseFromModelIterator(Iterator $transactionData)
    {
        foreach ($transactionData as $transactionDatum) {
            yield new cashApiTransactionResponseDto($transactionDatum);
        }
    }
}
