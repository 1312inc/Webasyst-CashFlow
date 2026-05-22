<?php

class cashAutomationSaveController extends cashJsonController
{
    public function execute()
    {
        $rules_data = waRequest::post('rules', [], waRequest::TYPE_ARRAY);

        list($rules_insert, $rules_update) = $this->prepareRules($rules_data);

        $oar_model = new cashAutomationModel();
        if ($rules_insert) {
            $oar_model->multipleInsert($rules_insert);
        }
        if ($rules_update) {
            foreach ($rules_update as $id => $rule) {
                $oar_model->updateById($id, $rule);
            }
        }

        $this->response = 'ok';
    }

    private function prepareRules($rules_data)
    {
        $insert = [];
        $update = [];
        foreach ($rules_data as $_rule_id => $_rule) {
            $_rule['app_id'] = ifset($_rule, 'app_id', cashConfig::APP_ID);
            $_rule['conditions'] = (isset($_rule['conditions']) ? array_values($_rule['conditions']) : null);
            if ($_rule_id < 1) {
                $insert[] = $_rule;
            } else {
                $update[$_rule_id] = $_rule;
            }
        }

        return [$insert, $update];
    }
}
