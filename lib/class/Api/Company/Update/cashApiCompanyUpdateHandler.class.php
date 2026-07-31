<?php

/**
 * cashApiCompanyUpdateHandler
 */
class cashApiCompanyUpdateHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiCompanyUpdateRequest $request
     * @return mixed
     * @throws waException
     */
    public function handle($request)
    {
        if ($request->getId() < 0) {
            throw new kmwaNotFoundException('Company not found');
        }

        $model = cash()->getModel('cashCompany');
        $company = $model->getById($request->getId());
        if (!$company) {
            throw new kmwaNotFoundException('Company not found');
        }

        if ($request->getName()) {
            $company['name'] = $request->getName();
        }
        if ($request->getSort()) {
            $company['sort'] = $request->getSort();
        }
        if (!$model->updateById($request->getId(), $company)) {
            throw new kmwaNotFoundException(_w('Company has not been updated'));
        }

        return $company;
    }
}
