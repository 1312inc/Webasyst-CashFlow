<?php

/**
 * Class cashCalculationService
 */
final class cashCalculationService
{
    /**
     * @param waContact     $contact
     * @param DateTime      $endDate
     * @param DateTime|null $startDate
     *
     * @return cashStatOnDateDto[]
     * @throws waException
     * @todo: cache?
     */
    public function getAccountStatsForDates(waContact $contact, DateTime $endDate, DateTime $startDate): array
    {
        /** @var cashAccountModel $model */
        $model = cash()->getModel(cashAccount::class);

        $data = $model->getStatDataForAccounts(
            $startDate->format('Y-m-d 00:00:00'),
            $endDate->format('Y-m-d H:i:s'),
            $contact
        );

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
     * @param waContact                                   $contact
     * @param cashAccount|cashCategory|cashAbstractEntity $entity
     * @param DateTime|null                               $startDate
     *
     * @return cashStatOnDateDto[]
     * @throws kmwaLogicException
     * @throws waException
     */
    public function getOnHandOnDate(
        DateTime $onDate,
        waContact $contact,
        cashAbstractEntity $entity,
        DateTime $startDate = null
    ): array {
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
            case $entity instanceof cashAccount:
                $data = $model->getStatDataForAccounts(
                    $startDate->format('Y-m-d H:i:s'),
                    $onDate->format('Y-m-d H:i:s'),
                    $contact,
                    $filterIds
                );
                break;

            case $entity instanceof cashCategory:
                $data = $model->getStatDataForCategories(
                    $startDate->format('Y-m-d H:i:s'),
                    $onDate->format('Y-m-d H:i:s'),
                    $contact,
                    $filterIds
                );
                break;

            case $entity instanceof cashImport:
                $data = $model->getStatDataForImport(
                    $startDate->format('Y-m-d H:i:s'),
                    $onDate->format('Y-m-d H:i:s'),
                    $contact,
                    $entity->getId()
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
                    'ids' => [],
                ];
            }
            $summaryData[$datum['currency']]['summary'] += $datum['summary'];
            $summaryData[$datum['currency']]['income'] += $datum['income'];
            $summaryData[$datum['currency']]['expense'] += $datum['expense'];
            $summaryData[$datum['currency']]['ids'][] = $datum['id'];
        }

        $summary = [];
        foreach ($summaryData as $currency => $summaryDatum) {
            if ($summaryDatum['summary'] == 0 && $filterIds && !array_intersect($filterIds, $summaryDatum['ids'])) {
                continue;
            }

            $summary[$currency] = new cashStatOnHandDto(
                cashCurrencyVO::fromWaCurrency($currency),
                new cashStatOnDateDto(
                    $summaryDatum['income'],
                    $summaryDatum['expense'],
                    $summaryDatum['summary']
                )
            );
        }

        return $summary;
    }

    /**
     * @param DateTime           $startDate
     * @param DateTime           $endDate
     * @param waContact          $contact
     * @param cashAbstractEntity $entity
     * @param string             $filterType
     *
     * @return array
     * @throws waException
     */
    public function getOnHandDetailedCategories(
        DateTime $startDate,
        DateTime $endDate,
        waContact $contact,
        cashAbstractEntity $entity,
        $filterType = cashTransactionPageFilterDto::FILTER_ACCOUNT
    ): array {
        /** @var cashAccountModel $model */
        $model = cash()->getModel(cashAccount::class);
        $filterIds = [];
        if ($entity->getId()) {
            $filterIds[] = $entity->getId();
        }

        $data = $model->getStatDetailedCategoryData(
            $startDate->format('Y-m-d H:i:s'),
            $endDate->format('Y-m-d H:i:s'),
            $contact,
            $filterType,
            $filterIds
        );

        return $this->generateSummaryOnHandDtos($data);
    }

    /**
     * @param DateTime  $startDate
     * @param DateTime  $endDate
     * @param waContact $contractor
     * @param waContact $contact
     *
     * @return array
     * @throws waException
     */
    public function getOnHandDetailedCategoriesForContractor(
        DateTime $startDate,
        DateTime $endDate,
        waContact $contractor,
        waContact $contact
    ): array {
        /** @var cashAccountModel $model */
        $model = cash()->getModel(cashAccount::class);

        $data = $model->getStatDetailedCategoryDataForContractor(
            $startDate->format('Y-m-d H:i:s'),
            $endDate->format('Y-m-d H:i:s'),
            $contractor,
            $contact
        );

        return $this->generateSummaryOnHandDtos($data);
    }

    /**
     * @param array $data
     *
     * @return array|cashStatOnHandDto[]
     */
    private function generateSummaryOnHandDtos(array $data): array
    {
        $summary = [];
        foreach ($data as $datum) {
            if (!isset($summary[$datum['id']])) {
                if (!$datum['id']) {
                    $datum['name'] = _w('No category');
                    $datum['color'] = cashCategoryFactory::NO_CATEGORY_COLOR;
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
