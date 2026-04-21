<?php

class cashPlanDeleteController extends cashJsonController
{
    public function execute()
    {
        $id = waRequest::post('id', null, waRequest::TYPE_INT);

        if ($id > 0) {
            $model = cash()->getModel('cashPlan');
            $model->deleteById($id);
        }
    }
}
