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
     * @param int $children_help_parents
     * @throws waException
     */
    public function __construct(array $data, array $currencies, int $children_help_parents)
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

        if ($children_help_parents) {
            foreach ($data as $_dt) {
                if (!empty($_dt['category_parent_id']) && !empty($data[$_dt['category_parent_id']])) {
                    if (empty($data[$_dt['category_parent_id']]['children_amount'])) {
                        $data[$_dt['category_parent_id']]['children_amount'] = $_dt['amount'];
                    } else {
                        $data[$_dt['category_parent_id']]['children_amount'] += $_dt['amount'];
                    }
                }
            }
        }
        foreach ($data as $graphDatum) {
            $categoryType = $categoryTypeMapping[$graphDatum['transaction_type']];

            $dataInfo = new cashApiAggregateGetBreakDownDataDto(
                $graphDatum,
                $this->getCategory($graphDatum['detailed'])
            );
            $response[$graphDatum['currency']][$categoryType]->data[] = $dataInfo;
            $response[$graphDatum['currency']][$categoryType]->totalAmount += $dataInfo->amount;
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
