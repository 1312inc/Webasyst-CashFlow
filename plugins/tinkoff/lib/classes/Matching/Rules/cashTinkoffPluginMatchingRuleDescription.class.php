<?php

class cashTinkoffPluginMatchingRuleDescription implements cashTinkoffPluginMatchingRuleInterface
{
    public function match(
        cashTransaction $cashTransaction,
        cashTinkoffPluginBankStatementOperationDto $operationDto,
        array $params = []
    ): bool {
        return $cashTransaction->getDescription() === $operationDto->getPaymentPurpose();
    }
}
