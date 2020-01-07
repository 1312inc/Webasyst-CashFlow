<?php

/**
 * Class cashAccountAction
 */
class cashAccountAction extends cashViewAction
{
    /**
     * @param null|array $params
     *
     * @return mixed
     * @throws kmwaAssertException
     * @throws kmwaNotFoundException
     * @throws waException
     */
    public function runAction($params = null)
    {
        $id = waRequest::get('id', 0, waRequest::TYPE_INT);
        $startDate = new DateTime(
            waRequest::request(
                'startDate',
                date('Y-m-d', strtotime('-90 days')),
                waRequest::TYPE_STRING_TRIM
            )
        );
        $endDate = new DateTime(
            waRequest::request(
                'startDate',
                date('Y-m-d', strtotime('+90 days')),
                waRequest::TYPE_STRING_TRIM
            )
        );

        if ($id) {
            $account = cash()->getEntityRepository(cashAccount::class)->findById($id);
            kmwaAssert::instance($account, cashAccount::class);
        } else {
            $account = cash()->getEntityFactory(cashAccount::class)->createAllAccount();
        }

        $accountDto = cashDtoFromEntityFactory::fromEntity(cashAccountDto::class, $account);

        $calcService = new cashCalculationService();

        // cash on hand today
        $onHands = $calcService->getOnHandOnDate(new DateTime(), $account);

        // total income
        // total expense

        $this->view->assign(
            [
                'account' => $accountDto,
                'onHands' => $onHands,
                'startDate' => $startDate->format('Y-m-d'),
                'endDate' => $endDate->format('Y-m-d'),
            ]
        );
    }
}
