<?php

/**
 * Class cashDdsPluginStatDto
 */
class cashDdsPluginStatDto
{
    /**
     * @var cashDdsPluginEntity
     */
    public $entity;

    /**
     * @var array
     */
    public $valuesPerPeriods;

    /**
     * @var array
     */
    public $currencies = [];

    /**
     * cashDdsPluginStatDto constructor.
     *
     * @param cashDdsPluginEntity $entity
     * @param array               $valuesPerPeriods
     */
    public function __construct(cashDdsPluginEntity $entity, array $valuesPerPeriods)
    {
        $this->entity = $entity;
        $this->valuesPerPeriods = $valuesPerPeriods;

        foreach ($valuesPerPeriods as $month => $valuesPerPeriod) {
            foreach (array_keys($valuesPerPeriod) as $currency) {
                if (!isset($this->currencies[$currency])) {
                    $this->currencies[$currency] = cashCurrencyVO::fromWaCurrency($currency);
                }
            }
        }
    }
}
