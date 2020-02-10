<?php

/**
 * Class cashCalculationService
 */
final class cashCalculationService
{
    /**
     * @param DateTime      $endDate
     * @param DateTime|null $startDate
     *
     * @return cashStatOnDateDto[]
     * @throws waException
     * @todo: cache?
     */
    public function getAccountStatsForDates(DateTime $endDate, DateTime $startDate = null)
    {
        /** @var cashAccountModel $model */
        $model = cash()->getModel(cashAccount::class);

        $startDate = ifset($startDate, new DateTime('1970-01-01'));
        $data = $model->getStatDataForAccounts($startDate->format('Y-m-d 00:00:00'), $endDate->format('Y-m-d H:i:s'));

        $dtos = [];
        foreach ($data as $datum) {
            /** @var cashStatOnDateDto $dto */
            $dtos[$datum['id']] = new cashStatOnDateDto(
                $datum['income'],
                $datum['expense'],
                $datum['summary']
            );
        }

        return $dtos;
    }

    /**
     * @param DateTime                                    $onDate
     * @param cashAccount|cashCategory|cashAbstractEntity $entity
     * @param DateTime|null                               $startDate
     *
     * @return cashStatOnDateDto[]
     * @throws kmwaLogicException
     * @throws waException
     */
    public function getOnHandOnDate(DateTime $onDate, cashAbstractEntity $entity, DateTime $startDate = null)
    {
        if (!$startDate instanceof DateTime) {
            $startDate = new DateTime('1970-01-01 00:00:00');
        }

        /** @var cashAccountModel $model */
        $model = cash()->getModel(cashAccount::class);
        $filterIds = [];
        if ($entity->getId()) {
            $filterIds[] = $entity->getId();
        }

        switch (true) {
            case $entity instanceOf cashAccount:
                $data = $model->getStatDataForAccounts(
                    $startDate->format('Y-m-d H:i:s'),
                    $onDate->format('Y-m-d H:i:s'),
                    $filterIds
                );
                break;

            case $entity instanceOf cashCategory:
                $data = $model->getStatDataForCategories(
                    $startDate->format('Y-m-d H:i:s'),
                    $onDate->format('Y-m-d H:i:s'),
                    $filterIds
                );
                break;

            default:
                throw new kmwaLogicException('Wrong filter entity');
        }

        $summaryData = [];
        foreach ($data as $datum) {
            if (!isset($summaryData[$datum['currency']])) {
                $summaryData[$datum['currency']] = [
                    'summary' => 0,
                    'income' => 0,
                    'expense' => 0,
                    'currency' => cashCurrencyVO::fromWaCurrency($datum['currency']),
                ];
            }
            $summaryData[$datum['currency']]['summary'] += $datum['summary'];
            $summaryData[$datum['currency']]['income'] += $datum['income'];
            $summaryData[$datum['currency']]['expense'] += $datum['expense'];
        }

        $summary = [];
        foreach ($summaryData as $currency => $summaryDatum) {
            if ($summaryDatum['summary'] != 0) {
                $summary[$currency] = new cashStatOnHandDto(
                    cashCurrencyVO::fromWaCurrency($currency),
                    new cashStatOnDateDto(
                        $summaryDatum['income'],
                        $summaryDatum['expense'],
                        $summaryDatum['summary']
                    )
                );
            }
        }

        return $summary;
    }

    public function getOnHandDetailedCategories(DateTime $startDate, DateTime $endDate, cashAbstractEntity $entity)
    {
        /** @var cashAccountModel $model */
        $model = cash()->getModel(cashAccount::class);
        $filterIds = [];
        if ($entity->getId()) {
            $filterIds[] = $entity->getId();
        }

        $filterType = 'account';
        if ($entity instanceOf cashCategory) {
            $filterType = 'category';
        }

        $data = $model->getStatDetailedCategoryData(
            $startDate->format('Y-m-d H:i:s'),
            $endDate->format('Y-m-d H:i:s'),
            $filterType,
            $filterIds
        );

        $summary = [];
        foreach ($data as $datum) {
            if (!isset($summary[$datum['id']])) {
                if (!$datum['id']) {
                    $datum['name'] = _w('No category');
                    $datum['color'] = cashColorStorage::DEFAULT_NO_CATEGORY_GRAPH_COLOR;
                }
                $category = new cashCategoryDto($datum);
                $summary[$datum['id']] = new cashStatCategoryDetailedDto($category);
            }

            $summary[$datum['id']]->stat[] = new cashStatOnHandDto(
                cashCurrencyVO::fromWaCurrency($datum['currency']),
                new cashStatOnDateDto(
                    $datum['income'],
                    $datum['expense'],
                    $datum['summary']
                )
            );
        }

        return $summary;
    }
}
