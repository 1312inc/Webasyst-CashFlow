<?php

/**
 * cashApiScenarioUpdateHandler
 */
class cashApiScenarioUpdateHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiScenarioUpdateRequest $request
     * @return array|mixed
     * @throws kmwaNotFoundException
     * @throws waException
     */
    public function handle($request)
    {
        if ($request->getId() < 0) {
            throw new kmwaNotFoundException('Scenario not found');
        }

        $model = cash()->getModel('cashScenario');
        $scenario = $model->getById($request->getId());
        if (!$scenario) {
            throw new kmwaNotFoundException('Scenario not found');
        }

        if ($request->getName()) {
            $scenario['name'] = $request->getName();
        }
        if ($request->getColor()) {
            $scenario['color'] = $request->getColor();
        }
        if ($request->getSort()) {
            $scenario['sort'] = $request->getSort();
        }
        if (!$model->updateById($request->getId(), $scenario)) {
            throw new kmwaNotFoundException(_w('Scenario has not been updated'));
        }

        return $scenario;
    }
}
