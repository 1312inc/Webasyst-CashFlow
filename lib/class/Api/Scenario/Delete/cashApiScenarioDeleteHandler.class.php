<?php

/**
 * cashApiScenarioDeleteHandler
 */
class  cashApiScenarioDeleteHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiScenarioDeleteRequest $request
     * @return true
     * @throws kmwaNotFoundException
     * @throws waException
     */
    public function handle($request)
    {
        $model = cash()->getModel('cashScenario');

        if (!$model->deleteById($request->getId())) {
            throw new kmwaNotFoundException(_w('Scenario has not been deleted yet'));
        }

        return true;
    }
}
