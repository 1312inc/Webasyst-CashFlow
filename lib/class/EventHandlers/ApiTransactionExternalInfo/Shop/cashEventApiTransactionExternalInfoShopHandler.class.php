<?php

class cashEventApiTransactionExternalInfoShopHandler implements cashEventApiTransactionExternalInfoHandlerInterface
{
    public function getResponse(
        cashApiTransactionResponseDto $cashApiTransactionResponseDto
    ): cashEventApiTransactionExternalInfoResponseInterface {
        return new cashEventApiTransactionExternalInfoResponse('green', 'Shop-Script');
    }

    public function getSource(): string
    {
        return 'shop';
    }
}
