<?php

/**
 * cashApiPlanGetResponse
 */
class cashApiPlanGetResponse extends cashApiAbstractResponse
{
    public function __construct(array $plans = [])
    {
        parent::__construct(200);

        $this->response = $this->filterFields(
            $plans,
             ['id', 'currency', 'account_id', 'category_id', 'from', 'to', 'amount', 'amount_fact'],
             [
                 'id' => 'int',
                 'account_id' => 'int',
                 'category_id' => 'int',
                 'amount' => 'float',
                 'amount_fact' => 'float',
             ]
        );
    }
}
