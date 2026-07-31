<?php

/**
 * cashApiAccountGetListRequest
 */
class cashApiAccountGetListRequest
{
    /**
     * @var int
     */
    private $company_id;

    public function __construct($company_id)
    {
        $this->company_id = (int) $company_id;
    }

    public function getCompanyId()
    {
        return $this->company_id;
    }
}
