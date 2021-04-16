<?php

interface cashEventApiTransactionExternalInfoHandlerInterface
{
    public function getSource(): string;

    public function getResponse(
        cashApiTransactionResponseDto $cashApiTransactionResponseDto
    ): cashEventApiTransactionExternalInfoResponseInterface;
}
