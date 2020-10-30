<?php

/**
 * Class cashApiTransactionResponseDtoAssembler
 */
class cashApiTransactionResponseDtoAssembler
{
    /**
     * @param Iterator $transactionData
     * @param float    $initialBalance
     * @param bool     $reverseOrder
     *
     * @return Generator<cashApiTransactionResponseDto>
     */
    public static function fromModelIteratorWithInitialBalance(Iterator $transactionData, $initialBalance, $reverseOrder = false)
    {
        foreach ($transactionData as $transactionDatum) {
            $dto= new cashApiTransactionResponseDto($transactionDatum);

            if ($initialBalance !== null && !isset($transactionDatum['balance'])) {
                $transactionDatum['balance'] = $initialBalance;
            }

            if ($initialBalance !== null) {
                if ($reverseOrder) {
                    $initialBalance -= $transactionDatum['amount'];
                } else {
                    $initialBalance += $transactionDatum['amount'];
                }
            }

            $dto->balance = $initialBalance;
            $dto->balanceShorten = cashShorteningService::money($dto->balance);

            yield $dto;
        }
    }

    /**
     * @param Iterator $transactionData
     *
     * @return Generator
     */
    public static function fromModelIterator(Iterator $transactionData)
    {
        foreach ($transactionData as $transactionDatum) {
            yield new cashApiTransactionResponseDto($transactionDatum);
        }
    }

    /**
     * @param cashTransaction $transaction
     *
     * @return cashApiTransactionResponseDto
     */
    public static function generateResponseFromEntity(cashTransaction $transaction): cashApiTransactionResponseDto
    {
        return cashDtoFromEntityFactory::fromEntity(cashApiTransactionResponseDto::class, $transaction);
    }
}
