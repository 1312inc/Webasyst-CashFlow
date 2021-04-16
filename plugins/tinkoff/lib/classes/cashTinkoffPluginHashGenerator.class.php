<?php

final class cashTinkoffPluginHashGenerator
{
    public function makeForTinkoffOperationDto(cashTinkoffPluginBankStatementOperationDto $operationDto): string
    {
        return md5(sprintf('%s|%s', 'tinkoff_plugin', serialize($operationDto)));
    }
}
