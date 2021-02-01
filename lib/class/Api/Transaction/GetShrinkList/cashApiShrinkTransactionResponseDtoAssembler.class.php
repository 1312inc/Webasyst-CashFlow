<?php

/**
 * Class cashApiShrinkTransactionResponseDtoAssembler
 */
class cashApiShrinkTransactionResponseDtoAssembler extends cashApiTransactionResponseDtoAbstractAssembler
{
    /**
     * @param Iterator $transactionData
     *
     * @return Generator<cashApiShrinkTransactionResponseDto>
     */
    public function fromModelIterator(Iterator $transactionData): Generator
    {
        foreach ($transactionData as $transactionDatum) {
            $dto = new cashApiShrinkTransactionResponseDto($transactionDatum);
            $dto->create_contact = $this->getContactData($dto->create_contact_id);
            if ($dto->contractor_contact_id) {
                $dto->contractor_contact = $this->getContactData($dto->contractor_contact_id);
            }

            yield $dto;
        }
    }
}
