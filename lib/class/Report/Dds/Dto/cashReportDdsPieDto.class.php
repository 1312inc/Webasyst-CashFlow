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

        if ($this->colors) {
            $data['colors'] = $this->colors;
        }

        return $data;
    }
}
