<?php

/**
 * Class cashApiSystemGetCurrenciesResponse
 */
class cashApiSystemGetCurrenciesResponse extends cashApiAbstractResponse
{
    /**
     * cashApiCategoryCreateResponse constructor.
     *
     * @param cashApiAggregateGetBreakDownDto[] $data
     */
    public function __construct(array $data)
    {
        parent::__construct(200);

        $this->response = $data;
    }
}
