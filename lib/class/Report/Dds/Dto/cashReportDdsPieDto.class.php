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
            'type' => 'pie',
            'columns' => array_values($this->columns),
        ];

        return $data;
    }
}
