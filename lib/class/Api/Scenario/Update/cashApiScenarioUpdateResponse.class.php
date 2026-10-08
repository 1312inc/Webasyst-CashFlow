<?php

/**
 * cashApiScenarioUpdateResponse
 */
class cashApiScenarioUpdateResponse extends cashApiAbstractResponse
{
    public function __construct(array $scenario = [])
    {
        parent::__construct(200);
        if (empty($scenario)) {
            parent::__construct(204);
            return;
        }

        $this->response = $this->singleFilterFields(
            $scenario,
            ['id', 'name', 'color', 'sort'],
            [
                'id' => 'int',
                'sort' => 'int',
            ]
        );
    }
}
