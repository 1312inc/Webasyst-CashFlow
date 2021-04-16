<?php

/**
 * Class cashApiAggregateGetBreakDownResponse
 */
final class cashApiAggregateGetBreakDownResponse extends cashApiAbstractResponse
{
    private $categories = null;

    /**
     * cashApiAggregateGetBreakDownResponse constructor.
     *
     * @param array $data
     * @param array $currencies
     *
     * @throws waException
     */
    public function __construct(array $data, array $currencies)
    {
        parent::__construct(200);

        $response = [];
        $categoryTypeMapping = [
            'expense|1' => 'profit',
            'expense|0' => 'expense',
            'income' => 'income',
        ];

        foreach ($currencies as $currency) {
            $response[$currency] = [
                'currency' => $currency,
                'income' => new cashApiAggregateGetBreakDownDto(),
                'expense' => new cashApiAggregateGetBreakDownDto(),
                'profit' => new cashApiAggregateGetBreakDownDto(),
            ];
        }

        foreach ($data as $graphDatum) {
            $categoryType = $categoryTypeMapping[$graphDatum['type']];

            $dataInfo = new cashApiAggregateGetBreakDownDataDto(
                $graphDatum['amount'],
                $this->getCategory($graphDatum['detailed'])
            );
            $response[$graphDatum['currency']][$categoryType]->data[] = $dataInfo;
            $response[$graphDatum['currency']][$categoryType]->totalAmount += abs($dataInfo->amount);
        }

        $this->response = array_values($response);
    }

    /**
     * @param int $id
     *
     * @return cashCategory|null
     * @throws waException
     */
    private function getCategory($id): ?cashCategory
    {
        if (null === $this->categories) {
            foreach (cash()->getEntityRepository(cashCategory::class)->findAllActiveForContact(wa()->getUser()) as $category) {
                $this->categories[$category->getId()] = $category;
            }
        }

        return $this->categories[$id] ?? null;
    }
}
