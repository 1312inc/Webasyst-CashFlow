<?php

/**
 * Class cashGraphCurrencyColumnDto
 */
class cashGraphCurrencyColumnDto extends cashAbstractDto
{
    /**
     * @var string
     */
    public $currency;

    /**
     * @var cashStatOnDateDto[]
     */
    public $accountStats;

    /**
     * cashGraphCurrencyColumnDto constructor.
     *
     * @param string $currency
     * @param array  $accountStats
     */
    public function __construct($currency, array $accountStats = [])
    {
        $this->currency = $currency;
        $this->accountStats = $accountStats;
    }
}
