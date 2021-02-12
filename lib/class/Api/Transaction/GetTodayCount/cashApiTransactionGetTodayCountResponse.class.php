<?php

/**
 * Class cashApiTransactionGetTodayCountResponse
 */
class cashApiTransactionGetTodayCountResponse extends cashApiAbstractResponse
{
    /**
     * cashApiTransactionGetTodayCountResponse constructor.
     *
     * @param cashApiTransactionGetTodayCountDto $dto
     */
    public function __construct(cashApiTransactionGetTodayCountDto $dto)
    {
        parent::__construct(200);

        $this->response = [
            'date' => $dto->date->format('Y-m-d'),
            'count' => [
                'onbadge' => $dto->onBadge,
                'today' => $dto->countOnDate
            ]
        ];
    }
}
