<?php

class cashAutomationLogController extends cashJsonController
{
    public function execute()
    {
        $rule_id = waRequest::post('rule_id', null, waRequest::TYPE_INT);

        if ($rule_id > 0) {
            $log_model = new cashAutomationLogModel();
            $logs = $log_model->getLogs($rule_id);

            foreach ($logs as &$_log) {
                $_log['detailed'] = $this->getDetailed($_log);
                unset($_log['automation_rule'], $_log['transaction']);
            }
            unset($_log);

            $this->response = $logs;
        }
    }

    /**
     * @param $log
     * @return array
     */
    private function getDetailed($log)
    {
        return [
            'automation_rule' => $this->format('automation_rule', $log),
            'transaction' => $this->format('transaction', $log),
        ];
    }

    /**
     * @param $name
     * @param $log
     * @return array
     */
    private function format($name, $log)
    {
        $result = [];
        if (empty($log[$name])) {
            return [];
        }

        switch ($name) {
            case 'transaction':
                foreach ($log[$name] as $_nm => $_log) {
                    if (is_scalar($_log)) {
                        $result[$_nm] = $_log;
                    }
                }
                break;
            case 'automation_rule':
                foreach ($log[$name] as $_nm => $_log) {
                    if (is_scalar($_log)) {
                        $result[$_nm] = $_log;
                    } elseif (is_array($_log)) {
                        $action = ifset($_log, 'action', '');

                        foreach ($_log as $_k => $_v) {
                            if (is_array($_v)) {
                                $result[$_nm][$_k] = var_export($_v, true);
                            } else {
                                $property = str_replace($action.'_property_', '', $_k);
                                $result[$property] = $_v;
                            }
                        }
                    }
                }
                break;
        }

        return $result;
    }
}
