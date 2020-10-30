<?php

/**
 * Class cashApiShrinkTransactionResponseDtoAssembler
 */
class cashApiShrinkTransactionResponseDtoAssembler
{
    /**
     * @param Iterator $transactionData
     *
     * @return Generator<cashApiShrinkTransactionResponseDto>
     */
    public static function fromModelIterator(Iterator $transactionData)
    {
        foreach ($transactionData as $transactionDatum) {
            yield new cashApiShrinkTransactionResponseDto($transactionDatum);
        }
    }
}
