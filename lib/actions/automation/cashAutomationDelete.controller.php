<?php

class cashAutomationDeleteController extends cashJsonController
{
    public function execute()
    {
        $rule_id = waRequest::post('rule_id', null, waRequest::TYPE_INT);

        if ($rule_id > 0) {
            $oar_model = new cashAutomationModel();
            $oar_model->deleteById($rule_id);
        }

        $this->response = 'ok';
    }
}
