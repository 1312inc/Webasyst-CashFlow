<?php

class cashEventHandlerApiTransactionExternalInfo implements cashEventApiTransactionExternalInfoHandlerInterface
{
    public function getResponse(
        cashApiTransactionResponseDto $cashApiTransactionResponseDto
    ): cashEventApiTransactionExternalInfoResponseInterface {
        return new cashEventApiTransactionExternalInfoResponse('yellow', 'Тинькофф', 'T');
    }

    public function getSource(): string
    {
        return 'tinkoff_business';
    }
}
