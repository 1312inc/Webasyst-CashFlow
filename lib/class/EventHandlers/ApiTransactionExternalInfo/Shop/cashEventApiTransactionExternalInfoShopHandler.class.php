<?php

class cashEventApiTransactionExternalInfoShopHandler implements cashEventApiTransactionExternalInfoHandlerInterface
{
    public function getResponse(
        cashApiTransactionResponseDto $cashApiTransactionResponseDto
    ): cashEventApiTransactionExternalInfoResponseInterface {
        return new cashEventApiTransactionExternalInfoResponse('green', 'Shop-Script', 'fas fa-shopping-cart');
    }

    public function getSource(): string
    {
        return 'shop';
    }
}
