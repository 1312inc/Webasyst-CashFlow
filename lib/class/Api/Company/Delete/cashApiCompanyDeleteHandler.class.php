<?php

/**
 * cashApiCompanyDeleteHandler
 */
class cashApiCompanyDeleteHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiCompanyDeleteRequest $request
     * @return true
     * @throws kmwaNotFoundException
     * @throws waException
     */
    public function handle($request)
    {
        $model = cash()->getModel('cashCompany');

        if (!$model->deleteById($request->getId())) {
            throw new kmwaNotFoundException(_w('Company has not been deleted yet'));
        }

        return true;
    }
}
