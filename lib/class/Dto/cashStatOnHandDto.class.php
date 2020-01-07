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
     * @var cashStatAccountDto
     */
    public $stat;

    /**
     * cashStatOnHandDto constructor.
     *
     * @param cashCurrencyVO     $currency
     * @param cashStatAccountDto $stat
     */
    public function __construct(cashCurrencyVO $currency, cashStatAccountDto $stat)
    {
        $this->currency = $currency;
        $this->stat = $stat;
    }
}
