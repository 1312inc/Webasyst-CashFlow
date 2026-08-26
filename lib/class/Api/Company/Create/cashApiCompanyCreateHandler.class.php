<?php

/**
 * cashApiCompanyCreateHandler
 */
class cashApiCompanyCreateHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiCompanyCreateRequest $request
     * @return array|mixed
     * @throws kmwaNotFoundException
     * @throws waException
     */
    public function handle($request)
    {
        $data = [
            'name' => $request->getName(),
            'sort' => $request->getSort(),
        ];

        $model = cash()->getModel('cashCompany');

        $data['id'] = $model->insert($data);
        if (!$data['id']) {
            throw new kmwaNotFoundException(_w('Company has not been saved'));
        }

        return $data;
    }
}
