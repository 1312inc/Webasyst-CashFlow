<?php

/**
 * cashApiScenarioCreateResponse
 */
class cashApiScenarioCreateResponse extends cashApiAbstractResponse
{
    /**
     * @param array $scenario
     */
    public function __construct(array $scenario = [])
    {
        parent::__construct(200);

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
