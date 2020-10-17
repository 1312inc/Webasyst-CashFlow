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

        $this->response = $data;
    }
}
