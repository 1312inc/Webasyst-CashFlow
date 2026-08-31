<?php

/**
 * cashApiScenarioCreateHandler
 */
class cashApiScenarioCreateHandler implements cashApiHandlerInterface
{
    /**
     * @param $request
     * @return array|mixed
     * @throws kmwaNotFoundException
     * @throws waException
     */
    public function handle($request)
    {
        $data = [
            'name'  => $request->getName(),
            'color' => $request->getColor(),
            'sort'  => $request->getSort(),
        ];

        $model = cash()->getModel('cashScenario');

        $data['id'] = $model->insert($data);
        if (!$data['id']) {
            throw new kmwaNotFoundException(_w('Scenario has not been saved'));
        }

        return $data;
    }
}
