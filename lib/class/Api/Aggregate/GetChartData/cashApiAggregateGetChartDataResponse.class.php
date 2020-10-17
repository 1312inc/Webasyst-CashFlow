<?php

/**
 * Class cashApiAggregateGetChartDataResponse
 */
class cashApiAggregateGetChartDataResponse extends cashApiAbstractResponse
{
    /**
     * cashApiCategoryCreateResponse constructor.
     *
     * @param cashApiAggregateGetBreakDownDto[] $data
     */
    public function __construct(array $data)
    {
        parent::__construct(200);

        $this->response = [
            'income' => ['total' => 0.0, 'data' => []],
            'expense' => ['total' => 0.0, 'data' => []],
        ];

        foreach ($data as $datum) {
            $this->response[$datum->direction]['data'][] = $datum;
            $this->response[$datum->direction]['total'] += $datum->amount;
        }
    }
}
