<?php

/**
 * cashApiPlanGetResponse
 */
class cashApiPlanGetResponse extends cashApiAbstractResponse
{
    public function __construct(array $plans = [])
    {
        parent::__construct(200);

        $this->response = $plans;
    }
}
