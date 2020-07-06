<?php

/**
 * Class cashTransactionBulkMoveDialogAction
 */
class cashTransactionBulkMoveDialogAction extends cashViewAction
{
    /**
     * @param null $params
     *
     * @return mixed|void
     * @throws kmwaLogicException
     * @throws kmwaNotFoundException
     * @throws waException
     */
    public function runAction($params = null)
    {
        $ids = waRequest::post('transaction_ids', '', waRequest::TYPE_STRING_TRIM);
        if (!$ids) {
            return;
        }

        $ids = json_decode($ids, true);
        $ids = array_filter($ids);
        if (!is_array($ids)) {
            return;
        }

        $filter = new cashTransactionPageFilterDto(waRequest::post('filterType'), waRequest::post('filterId'));

        $accountDtos = cashDtoFromEntityFactory::fromEntities(
            cashAccountDto::class,
            cash()->getEntityRepository(cashAccount::class)->findAllActive()
        );

        /** @var cashCategoryRepository $categoryRep */
        $categoryRep = cash()->getEntityRepository(cashCategory::class);
        $categoryDtosExpense = cashDtoFromEntityFactory::fromEntities(
            cashCategoryDto::class,
            $categoryRep->findAllExpense()
        );
        $categoryDtosIncome = cashDtoFromEntityFactory::fromEntities(
            cashCategoryDto::class,
            $categoryRep->findAllIncome()
        );

        $this->view->assign(
            [
                'transactionCount' => count($ids),
                'transactionIds' => implode(',',$ids),
                'accounts' => $accountDtos,
                'categoriesExpense' => $categoryDtosExpense,
                'categoriesIncome' => $categoryDtosIncome,
                'filter' => $filter,
            ]
        );
    }
}
