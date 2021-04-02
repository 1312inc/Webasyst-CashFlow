<?php

class cashEventHandlerApiTransactionExternalInfo implements cashEventApiTransactionExternalInfoHandlerInterface
{
    public function getResponse(
        cashApiTransactionResponseDto $cashApiTransactionResponseDto
    ): cashEventApiTransactionExternalInfoResponseInterface {
        return new cashEventApiTransactionExternalInfoResponse('yellow', 'Тинькофф');
    }

    public function getSource(): string
    {
        return 'tinkoff_business';
    }
}
