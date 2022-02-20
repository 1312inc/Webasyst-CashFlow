<?php

class cashEventApiTransactionExternalInfoCsvHandler implements cashEventApiTransactionExternalInfoHandlerInterface
{
    public function getResponse(
        cashApiTransactionResponseDto $cashApiTransactionResponseDto
    ): cashEventApiTransactionExternalInfoResponseInterface {

        return new cashEventApiTransactionExternalInfoResponse(random_int(0, 1312), '#499b5e', _w('Import via CSV'), 'fas fa-file-excel');
    }

    public function getSource(): string
    {
        return 'csv';
    }
}
