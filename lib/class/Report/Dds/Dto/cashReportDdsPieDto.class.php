<?php

/**
 * Class cashReportDdsPieDto
 */
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
     * cashReportDdsPieDto constructor.
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
    public function jsonSerialize()
    {
        $data = [
            'data' => [
                'type' => 'pie',
                'columns' => array_values($this->columns),
            ],
            'total' => $this->total,
            'helpers' => [
                'currencySign' => $this->currency->getSign(),
            ]
        ];

        if ($this->colors) {
            $data['colors'] = $this->colors;
        }

        return $data;
    }
}
