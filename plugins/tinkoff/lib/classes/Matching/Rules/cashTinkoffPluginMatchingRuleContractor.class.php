<?php

class cashTinkoffPluginMatchingRuleContractor implements cashTinkoffPluginMatchingRuleInterface
{
    public function match(
        cashTransaction $cashTransaction,
        cashTinkoffPluginBankStatementOperationDto $operationDto,
        array $params = []
    ): bool {
        /** @var cashUser $contact */
        $contact = cash()->getEntityFactory(cashUser::class)->getUser($cashTransaction->getContractorContactId());

        return $contact->getName() === $operationDto->getPayerName();
    }
}
