<?php

/**
 * Class cashApiTransactionGetBadgeCountResponse
 */
class cashApiTransactionGetBadgeCountResponse extends cashApiAbstractResponse
{
    /**
     * cashApiTransactionGetBadgeCountResponse constructor.
     *
     * @param cashApiTransactionGetBadgeCountDto $dto
     */
    public function __construct(cashApiTransactionGetBadgeCountDto $dto)
    {
        parent::__construct(200);

        $this->response = [
            'date' => $dto->date->format('Y-m-d'),
            'count' => [
                'onbadge' => $dto->count
            ]
        ];
    }
}
