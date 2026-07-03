<?php

/**
 * cashApiCompanyCreateResponse
 */
class cashApiCompanyCreateResponse extends cashApiAbstractResponse
{
    public function __construct(array $company = [])
    {
        parent::__construct(200);

        $this->response = $this->singleFilterFields(
            $company,
            ['id', 'name', 'sort'],
            [
                'id' => 'int',
                'sort' => 'int',
            ]
        );
    }
}
