<?php

/**
 * Class cashApiSystemGetCurrenciesResponse
 */
class cashApiSystemGetCurrenciesResponse extends cashApiAbstractResponse
{
    /**
     * cashApiCategoryCreateResponse constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct(200);

        $this->response = $data;
    }
}
