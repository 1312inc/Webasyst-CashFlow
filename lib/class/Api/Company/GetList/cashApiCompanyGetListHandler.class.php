<?php

/**
 * cashApiCompanyGetListHandler
 */
class cashApiCompanyGetListHandler implements cashApiHandlerInterface
{
    public function handle($request)
    {
        $model = cash()->getModel('cashCompany');

        return $model->getAll();
    }
}
