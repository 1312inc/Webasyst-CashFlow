<?php

/**
 * cashApiScenarioGetListHandler
 */
class cashApiScenarioGetListHandler implements cashApiHandlerInterface
{
    public function handle($request)
    {
        $model = cash()->getModel('cashScenario');

        return $model->getAll();
    }
}
