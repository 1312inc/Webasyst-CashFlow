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
        $handler_params = [];
        $plan_params = waRequest::param('params', '', waRequest::TYPE_STRING_TRIM);
        $plan_params = $plan_params ? explode('&', $plan_params) : [];
        foreach ($plan_params as $_plan_param) {
            $key_value = explode('=', $_plan_param);
            $handler_params[$key_value[0]] = null;
            if (count($key_value) === 2) {
                $handler_params[$key_value[0]] = $key_value[1];
            }
        }

        $this->handle($handler_params);
    }

    private function handle(array $params)
    {
        $report_service = new cashReportDdsService();

        $year = $params['year'] ?? 0;
        if (empty($year)) {
            $year = date('Y');
        }
        $current_period = cashReportPeriod::createForYear($year);

        $dds_types = $report_service->getTypes();
        /** @var cashReportDdsTypeDto|string $type */
        $type = $params['type'] ?? cashReportDdsService::TYPE_CATEGORY;
        if (isset($dds_types[$type])) {
            $type = $dds_types[$type];
        } else {
            throw new waException(sprintf('Unknown report type: %s', $type));
        }

        $periods = (new cashReportPeriodsFactory())->getPeriodsByYear();
        $data = $report_service->getDataForTypeAndPeriod($type, $current_period);
        $type = array_reduce($data, static function ($type, cashReportDdsStatDto $dto) {
            if ($dto->entity->isIncome()) {
                $type->incomeEntities++;
            }
            if ($dto->entity->isExpense()) {
                $type->expenseEntities++;
            }

            return $type;
        }, $type);

        $this->view->assign([
            'type' => $type,
            'data' => $data,
            'dds_types' => $dds_types,
            'report_periods' => $periods,
            'current_period' => $current_period,
            'grouping' => $current_period->getGrouping(),
            'is_imaginary' => in_array(true, array_unique(array_column($data, 'is_imaginary')), true)
        ]);
    }
}
