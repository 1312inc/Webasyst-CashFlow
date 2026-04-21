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
        $report_service = new cashReportDdsService();
        $current_period = cashReportPeriod::createForYear($plan_year);
        $dds_types = $report_service->getTypes();
        $type = $dds_types[cashReportDdsService::TYPE_CATEGORY];
        $periods = (new cashReportPeriodsFactory())->getPeriodsByYear();
        $data = $report_service->getDataForTypeAndPeriod($type, $current_period);

        $plans_by_month = [];
        $model = cash()->getModel('cashPlan');
        $plans = $model->select('id, currency, category_id, MONTH(`month`) `month`, amount')
            ->where('`month` BETWEEN s:date_start AND s:date_end', ['date_start' => $plan_year.'-01-01', 'date_end' => $plan_year.'-12-31'])
            ->order('`month`, currency, category_id')
            ->fetchAll();

        foreach ($plans as $_plan) {
            $plans_by_month[$_plan['month']][$_plan['currency']][] = $_plan;
        }
        unset($plans);

        foreach ($data as $_data) {
            if ($_data->entity->isHeader()) {
                continue;
            }
            foreach ($_data->valuesPerPeriods as $_month => $values_per_period) {
                foreach ($values_per_period as $_currency => $_value_per_period) {
                    $_data->valuesPerPeriods[$_month][$_currency]['plan'] = [];
                    if (isset($plans_by_month[$_month][$_currency])) {
                        foreach ($plans_by_month[$_month][$_currency] as $_plan) {
                            if ($_plan['category_id'] == $_value_per_period['id'] && $_plan['month'] == $_value_per_period['month']) {
                                $_data->valuesPerPeriods['plans'][$_month][$_currency] = $_plan;
                            }
                        }
                    }
                }
            }
        }

        $this->view->assign([
            'data' => $data,
            'report_periods' => $periods,
            'current_period' => $current_period,
            'grouping' => $current_period->getGrouping(),
            'account_currencies' => cash()->getModel(cashAccount::class)->getCurrencies(),
            'is_imaginary' => in_array(true, array_unique(array_column($data, 'is_imaginary')), true)
        ]);
    }
}
