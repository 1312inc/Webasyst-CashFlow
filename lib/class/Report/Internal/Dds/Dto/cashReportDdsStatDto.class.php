<?php

final class cashReportDdsStatDto
{
    /**
     * @var cashReportDdsEntity
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
     * cashReportDdsServiceStatDto constructor.
     *
     * @param cashReportDdsEntity $entity
     * @param array               $valuesPerPeriods
     */
    public function __construct(cashReportDdsEntity $entity, array $valuesPerPeriods)
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
