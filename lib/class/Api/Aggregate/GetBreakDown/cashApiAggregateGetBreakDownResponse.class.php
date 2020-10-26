<?php

/**
 * Class cashApiAggregateGetBreakDownResponse
 */
class cashApiAggregateGetBreakDownResponse extends cashApiAbstractResponse
{
    /**
     * cashApiAggregateGetBreakDownResponse constructor.
     *
     * @param cashApiAggregateGetBreakDownDto[] $data
     */
    public function __construct(array $data)
    {
        parent::__construct(200);

        $this->response = [
            'income' => [],
            'expense' => [],
        ];

        foreach ($data as $item) {
            if (!isset($this->response[$item->direction][$item->currency])) {
                $this->response[$item->direction][$item->currency] = ['total' => 0.0, 'data' => []];
            }
            $this->response[$item->direction][$item->currency]['data'][] = $item;
            $this->response[$item->direction][$item->currency]['total'] += $item->amount;
        }

        $this->response = [
            'income' => array_values($this->response['income']),
            'expense' => array_values($this->response['expense']),
        ];
    }
}
