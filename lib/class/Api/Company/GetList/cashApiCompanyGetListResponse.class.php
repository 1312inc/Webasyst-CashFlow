<?php

/**
 * cashApiCompanyGetListResponse
 */
class cashApiCompanyGetListResponse extends cashApiAbstractResponse
{
    public function __construct(array $companies = [])
    {
        parent::__construct(200);

        $this->response = $this->filterFields(
            $companies,
            ['id', 'name', 'sort'],
            [
                'id' => 'int',
                'sort' => 'int',
            ]
        );
    }
}
