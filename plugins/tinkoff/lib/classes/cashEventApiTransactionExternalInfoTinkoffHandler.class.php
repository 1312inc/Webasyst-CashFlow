<?php

class cashEventApiTransactionExternalInfoTinkoffHandler implements cashEventApiTransactionExternalInfoHandlerInterface
{
    private $source;

    /**
     * @param string $external_source
     */
    public function __construct($external_source)
    {
        $this->source = $external_source;
    }

    public function getResponse(cashApiTransactionResponseDto $cashApiTransactionResponseDto): cashEventApiTransactionExternalInfoResponseInterface
    {
        return new cashEventApiTransactionExternalInfoResponse(
            $cashApiTransactionResponseDto->id,
            '#ffdd2e',
            _w('Тинькофф банк'),
            '',
            '',
            wa()->getAppStaticUrl().'plugins/tinkoff/img/tinkoff_circle.svg'
        );
    }

    public function getSource(): string
    {
        return $this->source;
    }
}
