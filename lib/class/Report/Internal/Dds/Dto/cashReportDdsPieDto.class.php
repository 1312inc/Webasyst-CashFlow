<?php

class cashReportDdsPieDto implements JsonSerializable
{
    /**
     * @var cashCurrencyVO
     */
    public $currency;

    /**
     * @var array
     */
    public $columns = [];

    /**
     * @var array
     */
    public $colors = [];

    /**
     * @var array
     */
    public $total = [];

    /**
     * cashReportDdsServicePieDto constructor.
     *
     * @param cashCurrencyVO $currency
     */
    public function __construct(cashCurrencyVO $currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return mixed|void
     */
    #[ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $data = [
            'data' => [
                'type' => 'pie',
                'columns' => $this->columns,
            ],
            'total' => $this->total,
            'helpers' => [
                'currencySign' => $this->currency->getSign(),
            ]
        ];

        if ($this->colors) {
            $data['data']['colors'] = $this->colors;
        }

        return $data;
    }
}
