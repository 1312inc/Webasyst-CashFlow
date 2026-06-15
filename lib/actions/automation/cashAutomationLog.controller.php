<?php

class cashAutomationLogController extends cashJsonController
{
    public function execute()
    {
        $rule_id = waRequest::post('rule_id', null, waRequest::TYPE_INT);

        if ($rule_id > 0) {
            $log_model = new cashAutomationLogModel();
            $this->response = $log_model->getLogs($rule_id);
        }
    }
}
