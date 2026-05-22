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
     * @param cashApiAggregateGetBreakDownRequest $request
     * @throws waException
     */
    public function __construct(array $data, array $currencies, cashApiAggregateGetBreakDownRequest $request)
    {
        parent::__construct(200);

        $response = [];
        $categoryTypeMapping = [
            'expense|1' => 'profit',
            'expense|0' => 'expense',
            'income|0' => 'income',
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

        if ($request->children_help_parents) {
            $children_amounts = [];
            $category_ids = array_column($data, 'detailed');
            $this->getCategory(0);
            foreach ($data as $_dt) {
                if (!empty($_dt['category_parent_id'])) {
                    $parent_category_ids[$_dt['category_parent_id']] = 1;
                    if (empty($children_amounts[$_dt['currency']][$_dt['category_parent_id']])) {
                        $children_amounts[$_dt['currency']][$_dt['category_parent_id']] = 0;
                        if (!in_array($_dt['category_parent_id'], $category_ids)) {
                            $_c = $this->getCategory($_dt['category_parent_id']);
                            $data[] = [
                                'amount' => 0,
                                'currency' => $_dt['currency'],
                                'detailed' => $_dt['category_parent_id'],
                                'category_parent_id' => null,
                                'transaction_type' => $_c->getType().'|'.($_c->getIsProfit() ? 1 : 0),
                            ];
                        }
                    }
                    $children_amounts[$_dt['currency']][$_dt['category_parent_id']] += $_dt['amount'];
                }
            }
        }
        foreach ($data as $graphDatum) {
            $categoryType = $categoryTypeMapping[$graphDatum['transaction_type']];

            $dataInfo = new cashApiAggregateGetBreakDownDataDto(
                $graphDatum,
                $this->getCategory($graphDatum['detailed'])
            );
            if ($request->children_help_parents && !empty($children_amounts[$graphDatum['currency']][$graphDatum['detailed']])) {
                $dataInfo->amount += $children_amounts[$graphDatum['currency']][$graphDatum['detailed']];
            }
            $response[$graphDatum['currency']][$categoryType]->data[] = $dataInfo;
            if (!$request->children_help_parents || empty($graphDatum['category_parent_id'])) {
                $response[$graphDatum['currency']][$categoryType]->totalAmount += $dataInfo->amount;
            }
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
