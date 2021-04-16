<?php

interface cashTinkoffPluginMatchingRuleInterface
{
    public function match(
        cashTransaction $cashTransaction,
        cashTinkoffPluginBankStatementOperationDto $operationDto,
        array $params = []
    ): bool;
}
