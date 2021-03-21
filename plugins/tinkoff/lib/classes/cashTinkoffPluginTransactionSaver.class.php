<?php

final class cashTinkoffPluginTransactionSaver
{
    public const EXTERNAL_SOURCE = 'tinkoff_business';

    /**
     * @var cashTinkoffPluginHashGenerator
     */
    private $hashGenerator;

    public function __construct()
    {
        $this->hashGenerator = new cashTinkoffPluginHashGenerator();
    }

    public function makeTransactionCreateRequestFromTinkoffOperation(
        cashTinkoffPluginBankStatementOperationDto $operationDto
    ): cashApiTransactionCreateRequest {
        $createRequest = new cashApiTransactionCreateRequest();

        $createRequest->date = $operationDto->getDate()->format('Y-m-d');
        $createRequest->category_id = 1;
        $createRequest->amount = $operationDto->getAmount();
        $createRequest->description = $operationDto->getPaymentPurpose();
        $createRequest->account_id = 1;

        return $createRequest;
    }

    public function findExistingTransactionByOperation(
        cashTinkoffPluginBankStatementOperationDto $operationDto
    ): ?cashTransaction {
        /** @var cashTransaction|null $t */
        $t = cash()->getEntityRepository(cashTransaction::class)->findByFields(
            [
                'external_source' => self::EXTERNAL_SOURCE,
                'external_hash' => $this->hashGenerator->makeForTinkoffOperationDto($operationDto),
            ]
        );

        return $t;
    }

    public function findMatchingTransactionByOperation(
        cashTinkoffPluginBankStatementOperationDto $operationDto
    ): ?cashTransaction {
        /** @var cashTransaction|null $t */
        $t = cash()->getEntityRepository(cashTransaction::class)->findByFields(
            [
                'external_source' => self::EXTERNAL_SOURCE,
                'external_hash' => $this->hashGenerator->makeForTinkoffOperationDto($operationDto),
            ]
        );

        return $t;
    }

    public function saveTransaction(cashTinkoffPluginBankStatementOperationDto $operationDto): ?cashTransaction
    {
        try {
            $createRequest = $this->makeTransactionCreateRequestFromTinkoffOperation($operationDto);
            $created = (new cashApiTransactionCreateHandler())->handle($createRequest);

            $created = reset($created);

            /** @var cashTransaction $transaction */
            $transaction = cash()->getEntityRepository(cashTransaction::class)->findById($created->id);
            if (!$transaction) {
                return null;
            }

            $hash = $this->hashGenerator->makeForTinkoffOperationDto($operationDto);

            $transaction
                ->setExternalSource(self::EXTERNAL_SOURCE)
                ->setExternalHash($hash)
                ->setExternalData($operationDto->toArray());
        } catch (Exception $exception) {
            cashTinkoffPlugin::log($exception->getMessage());
            cashTinkoffPlugin::log($exception->getTraceAsString());
        }

        return null;
    }
}
