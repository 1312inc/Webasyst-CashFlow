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

    /**
     * @var int
     */
    private $scenario_id;

    public function __construct($company_id, $scenario_id)
    {
        $this->company_id = (int) $company_id;
        $this->scenario_id = (int) $scenario_id;
    }

    public function getCompanyId()
    {
        return $this->company_id;
    }

    public function getScenarioId()
    {
        return $this->scenario_id;
    }
}
