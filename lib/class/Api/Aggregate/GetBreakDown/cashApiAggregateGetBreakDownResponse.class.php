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
     */
    public function __construct(array $data)
    {
        parent::__construct(200);

        $response = [];
        foreach ($data as $graphDatum) {
            if (!isset($response[$graphDatum['currency']])) {
                $response[$graphDatum['currency']] = [
                    'currency' => $graphDatum['currency'],
                    'income' => new cashApiAggregateGetBreakDownDto(),
                    'expense' => new cashApiAggregateGetBreakDownDto(),
                ];
            }

            $dataInfo = new cashApiAggregateGetBreakDownDataDto(
                $graphDatum['amount'],
                $this->getCategory($graphDatum['detailed'])
            );
            $response[$graphDatum['currency']][$graphDatum['type']]->data[] = $dataInfo;
            $response[$graphDatum['currency']][$graphDatum['type']]->totalAmount += abs($dataInfo->amount);
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
