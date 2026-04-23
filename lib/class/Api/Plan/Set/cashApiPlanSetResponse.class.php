<?php

/**
 * cashApiPlanSetResponse
 */
class cashApiPlanSetResponse extends cashApiAbstractResponse
{
    public function __construct(array $plan = [])
    {
        parent::__construct(200);
        if (empty($plan)) {
            parent::__construct(204);
            return;
        }

        $this->response = $this->singleFilterFields(
            $plan,
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
