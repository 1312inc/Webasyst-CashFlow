<?php

/**
 * Class cashApiTransactionGetResponseDtoAssembler
 */
class cashApiTransactionGetResponseDtoAssembler extends cashApiTransactionResponseDtoAbstractAssembler
{
    /**
     * @param cashTransaction $transaction
     *
     * @return cashApiTransactionGetResponseDto
     */
    public function generateResponseFromEntity(cashTransaction $transaction): cashApiTransactionGetResponseDto
    {
        /** @var cashApiTransactionGetResponseDto $dto */
        $dto = cashDtoFromEntityFactory::fromEntity(cashApiTransactionGetResponseDto::class, $transaction);
        $dto->create_contact = $this->getContactData($dto->create_contact_id);
        if ($dto->contractor_contact_id) {
            $dto->contractor_contact = $this->getContactData($dto->contractor_contact_id);
        }

        return $dto;
    }
}
