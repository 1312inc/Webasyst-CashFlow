<?php

final class cashApiAggregateGetBalanceFlowResponse extends cashApiAbstractResponse
{
    /**
     * cashApiCategoryCreateResponse constructor.
     *
     * @param cashApiAggregateGetBalanceFlowDto[] $data
     */
    public function __construct(array $data)
    {
        parent::__construct(200);

        $this->response = $data;
    }
}
