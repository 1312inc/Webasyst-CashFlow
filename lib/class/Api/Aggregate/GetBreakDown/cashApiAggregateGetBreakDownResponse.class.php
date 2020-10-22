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

        foreach ($data as $datum) {
            foreach ($datum as $item) {
                if (!isset($this->response[$item->direction][$item->currency])) {
                    $this->response[$item->direction][$item->currency] = ['total' => 0.0, 'data' => []];
                }
                $this->response[$item->direction][$item->currency]['data'][] = $item;
                $this->response[$item->direction][$item->currency]['total'] += $item->amount;
            }
        }
    }
}
