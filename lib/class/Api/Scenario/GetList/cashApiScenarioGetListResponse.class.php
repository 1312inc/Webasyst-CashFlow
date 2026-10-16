<?php

/**
 * cashApiScenarioGetListResponse
 */
class cashApiScenarioGetListResponse extends cashApiAbstractResponse
{
    public function __construct(array $scenarios = [])
    {
        parent::__construct(200);

        $this->response = $this->filterFields(
            $scenarios,
            ['id', 'name', 'color', 'sort', 'count_transaction'],
            [
                'id' => 'int',
                'sort' => 'int',
                'count_transaction' => 'int'
            ]
        );
    }
}
