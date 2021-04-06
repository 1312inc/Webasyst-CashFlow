<?php

class cashEventApiTransactionExternalInfoCsvHandler implements cashEventApiTransactionExternalInfoHandlerInterface
{
    public function getResponse(
        cashApiTransactionResponseDto $cashApiTransactionResponseDto
    ): cashEventApiTransactionExternalInfoResponseInterface {

        return new cashEventApiTransactionExternalInfoResponse('#499b5e', 'Import via CSV', 'fas fa-file-excel');
    }

    public function getSource(): string
    {
        return 'csv';
    }
}
