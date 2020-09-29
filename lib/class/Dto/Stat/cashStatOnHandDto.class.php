<?php

/**
 * Class cashStatOnHandDto
 */
class cashStatOnHandDto
{
    /**
     * @var cashCurrencyVO
     */
    public $currency;

    /**
     * @var cashStatOnDateDto
     */
    public $stat;

    /**
     * cashStatOnHandDto constructor.
     *
     * @param cashCurrencyVO    $currency
     * @param cashStatOnDateDto $stat
     */
    public function __construct(cashCurrencyVO $currency, cashStatOnDateDto $stat)
    {
        $this->currency = $currency;
        $this->stat = $stat;
    }
}
