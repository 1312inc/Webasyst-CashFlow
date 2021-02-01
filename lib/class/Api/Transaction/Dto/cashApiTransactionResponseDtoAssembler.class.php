<?php

/**
 * Class cashApiTransactionResponseDtoAssembler
 */
class cashApiTransactionResponseDtoAssembler extends cashApiTransactionResponseDtoAbstractAssembler
{
    /**
     * @param Iterator $transactionData
     * @param float    $initialBalance
     * @param bool     $reverseOrder
     *
     * @return Generator<cashApiTransactionResponseDto>
     */
    public function fromModelIteratorWithInitialBalance(
        Iterator $transactionData,
        $initialBalance,
        $reverseOrder = false
    ): Generator {
        foreach ($transactionData as $transactionDatum) {
            $dto = new cashApiTransactionResponseDto($transactionDatum);

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

            $dto->create_contact = $this->getContactData($dto->create_contact_id);
            if ($dto->contractor_contact_id) {
                $dto->contractor_contact = $this->getContactData($dto->contractor_contact_id);
            }

            yield $dto;
        }
    }

    /**
     * @param Iterator $transactionData
     *
     * @return Generator
     */
    public function fromModelIterator(Iterator $transactionData): Generator
    {
        foreach ($transactionData as $transactionDatum) {
            $dto = new cashApiTransactionResponseDto($transactionDatum);
            $dto->create_contact = $this->getContactData($dto->create_contact_id);
            if ($dto->contractor_contact_id) {
                $dto->contractor_contact = $this->getContactData($dto->contractor_contact_id);
            }

            yield $dto;
        }
    }

    /**
     * @param cashTransaction $transaction
     *
     * @return cashApiTransactionResponseDto
     */
    public function generateResponseFromEntity(cashTransaction $transaction): cashApiTransactionResponseDto
    {
        /** @var cashApiTransactionResponseDto $dto */
        $dto = cashDtoFromEntityFactory::fromEntity(cashApiTransactionResponseDto::class, $transaction);
        $dto->create_contact = $this->getContactData($dto->create_contact_id);
        if ($dto->contractor_contact_id) {
            $dto->contractor_contact = $this->getContactData($dto->contractor_contact_id);
        }

        return $dto;
    }
}
