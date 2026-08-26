<?php

/**
 * cashApiCompanyUpdateResponse
 */
class cashApiCompanyUpdateResponse extends cashApiAbstractResponse
{
    public function __construct(array $company = [])
    {
        parent::__construct(200);
        if (empty($company)) {
            parent::__construct(204);
            return;
        }

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
