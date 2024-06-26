<?php

class cashEventApiTransactionExternalInfoCsvHandler implements cashEventApiTransactionExternalInfoHandlerInterface
{
    public function getResponse(cashApiTransactionResponseDto $cashApiTransactionResponseDto): cashEventApiTransactionExternalInfoResponseInterface
    {
        return new cashEventApiTransactionExternalInfoResponse(
            '',
            '#499b5e',
            _w('Import via CSV'),
            'fas fa-file-excel',
            '',
            '',
            _w('Import via CSV')
        );
    }

    public function getSource(): string
    {
        return 'csv';
    }
}
