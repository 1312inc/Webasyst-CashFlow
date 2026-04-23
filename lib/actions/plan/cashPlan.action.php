<?php

class cashPlanAction extends cashViewAction
{
    public function preExecute()
    {
        if (wa()->whichUI() === '2.0') {
            $this->setLayout(new cashStaticLayout());
        } elseif (!waRequest::isXMLHttpRequest() && wa()->whichUI() === '1.3') {
            $this->redirect(wa()->getAppUrl());
        }

        parent::preExecute();
    }

    public function runAction($params = null)
    {
        $plan_year = waRequest::get('year', 0, waRequest::TYPE_INT);

        if (empty($plan_year)) {
            $plan_year = date('Y');
        }
        $plans_by_month = [];
        $plan_service = new cashPlanService($plan_year);
        $data = $plan_service->getDataForPeriod();
        $model = cash()->getModel('cashPlan');
        $plans = $model->select('id, currency, category_id, MONTH(`month`) `month`, amount')
            ->where('`month` IS NULL OR (`month` >= s:date_start AND `month` < s:date_end)', ['date_start' => $plan_service->getPeriod()->getStart()->format('Y-m-d'), 'date_end' => $plan_service->getPeriod()->getEnd()->format('Y-m-d')])
            ->order('`month`, currency, category_id')
            ->fetchAll();

        foreach ($plans as $_plan) {
            if (empty($_plan['month'])) {
                for ($_m = 1; $_m < 13; $_m++) {
                    $plans_by_month[$_m][$_plan['currency']][] = $_plan;
                }
            } else {
                $plans_by_month[$_plan['month']][$_plan['currency']][] = $_plan;
            }
        }
        unset($plans);

        foreach ($data as $_data) {
            if ($_data->entity->isHeader()) {
                continue;
            }
            foreach ($_data->valuesPerPeriods as $_month => $values_per_period) {
                if (!is_numeric($_month)) {
                    continue;
                }
                foreach ($values_per_period as $_currency => $_value_per_period) {
                    $_data->valuesPerPeriods[$_month][$_currency]['plan'] = [];
                    if (isset($plans_by_month[$_month][$_currency])) {
                        foreach ($plans_by_month[$_month][$_currency] as $_plan) {
                            if ($_plan['category_id'] == $_value_per_period['id'] && ($_plan['month'] == $_value_per_period['month'] || $_plan['month'] === null)) {
                                $_data->valuesPerPeriods['plans'][$_month][$_currency] = $_plan;
                            }
                        }
                    }
                }
            }
        }

        $this->view->assign([
            'data' => $data,
            'report_periods' => $plan_service->getPeriodsByYear(),
            'current_period' => $plan_service->getPeriod(),
            'grouping' => $plan_service->getPeriod()->getGrouping(),
            'account_currencies' => cash()->getModel(cashAccount::class)->getCurrencies(),
            'is_imaginary' => in_array(true, array_unique(array_column($data, 'is_imaginary')), true)
        ]);
    }
}
