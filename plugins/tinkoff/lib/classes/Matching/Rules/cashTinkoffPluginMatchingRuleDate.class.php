<?php

class cashTinkoffPluginMatchingRuleDate implements cashTinkoffPluginMatchingRuleInterface
{
    public function match(
        cashTransaction $cashTransaction,
        cashTinkoffPluginBankStatementOperationDto $operationDto,
        array $params = []
    ): bool {
        return $cashTransaction->getDate() === $operationDto->getDate()->format('Y-m-d');
    }
}
