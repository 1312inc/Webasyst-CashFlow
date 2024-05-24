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
            $dto->balance = $initialBalance;
            $dto->balanceShorten = cashShorteningService::money($dto->balance);
            if ($initialBalance !== null) {
                if ($reverseOrder) {
                    $initialBalance -= $transactionDatum['amount'];
                } else {
                    $initialBalance += $transactionDatum['amount'];
                }
            }

            $dto->create_contact = $this->getContactData($dto->create_contact_id);
            if ($dto->contractor_contact_id) {
                $dto->contractor_contact = $this->getContactData($dto->contractor_contact_id);
            }

            if (!empty($dto->external_data['id']) && $dto->external_source === 'shop') {
                $dto->external_data['info'] = $this->getShopData($dto->external_data);
            }

            if (!empty($dto->repeating_id)) {
                $dto->repeating_data = $this->getRepeatingData($dto->repeating_id);
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
            yield $this->fromData($transactionDatum);
        }
    }

    /**
     * @param array $transactionDatum
     *
     * @return cashApiTransactionResponseDto
     */
    public function fromData(array $transactionDatum): cashApiTransactionResponseDto
    {
        $dto = new cashApiTransactionResponseDto($transactionDatum);
        $dto->create_contact = $this->getContactData($dto->create_contact_id);
        if ($dto->contractor_contact_id) {
            $dto->contractor_contact = $this->getContactData($dto->contractor_contact_id);
        }

        if (!empty($dto->repeating_id)) {
            $dto->repeating_data = $this->getRepeatingData($dto->repeating_id);
        }

        return $dto;
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

        if (!empty($dto->repeating_id)) {
            $dto->repeating_data = $this->getRepeatingData($dto->repeating_id);
        }

        return $dto;
    }
}
