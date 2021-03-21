<?php

final class cashTinkoffPluginHashGenerator
{
    public function makeForTinkoffOperationDto(cashTinkoffPluginBankStatementOperationDto $operationDto): string
    {
        return sprintf('%s|%s', 'tinkoff_plugin', $operationDto->getPayerName());
    }
}
