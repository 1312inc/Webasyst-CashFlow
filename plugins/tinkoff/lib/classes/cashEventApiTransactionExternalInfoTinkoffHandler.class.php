<?php

class cashEventApiTransactionExternalInfoTinkoffHandler implements cashEventApiTransactionExternalInfoHandlerInterface
{
    private $id;
    private $source;

    /**
     * @param int $id
     * @param string $external_source
     */
    public function __construct($id, $external_source)
    {
        $this->id = $id;
        $this->source = $external_source;
    }

    public function getResponse(cashApiTransactionResponseDto $cashApiTransactionResponseDto): cashEventApiTransactionExternalInfoResponseInterface
    {
        return new cashEventApiTransactionExternalInfoResponse(
            $this->id,
            '#ff0000',
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
