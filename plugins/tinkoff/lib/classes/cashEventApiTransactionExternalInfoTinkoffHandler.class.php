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
            '',
            '#ffdd2e',
            _w('Т-Бизнес'),
            '',
            '',
            wa()->getAppStaticUrl().'plugins/tinkoff/img/tinkoff_circle.svg',
            _w('Т-Бизнес')
        );
    }

    public function getSource(): string
    {
        return $this->source;
    }
}
