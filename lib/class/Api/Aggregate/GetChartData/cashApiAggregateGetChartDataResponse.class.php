<?php

/**
 * Class cashApiAggregateGetChartDataResponse
 */
class cashApiAggregateGetChartDataResponse extends cashApiAbstractResponse
{
    /**
     * cashApiCategoryCreateResponse constructor.
     *
     * @param cashApiAggregateGetChartDataDto[] $data
     */
    public function __construct(array $data)
    {
        parent::__construct(200);

        $this->response = $data;
    }
}
