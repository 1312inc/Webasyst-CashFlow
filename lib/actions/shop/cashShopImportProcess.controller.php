<?php

/**
 * Class cashShopImportProcessController
 *
 * @todo move process to service
 * @property cashShopImportProcessDto data['info']
 */
class cashShopImportProcessController extends waLongActionController
{
    const ORDERS_TO_PROCEED = 30;

    public function execute()
    {
        try {
            if (!cash()->getUser()->isAdmin()) {
                throw new kmwaForbiddenException();
            }

            parent::execute();
        } catch (Exception $ex) {
            cash()->getLogger()->error($ex->getMessage(), $ex, 'shop/import');
            $this->getResponse()->addHeader('Content-type', 'application/json');
            echo json_encode(['error' => $ex->getMessage()]);
            exit;
        }
    }

    /**
     * @throws kmwaRuntimeException
     * @throws waException
     */
    protected function init()
    {
        cash()->getLogger()->debug('INIT start', 'shop/import');

        $shopIntegration = new cashShopIntegration();
        if (!$shopIntegration->shopExists()) {
            throw new kmwaRuntimeException('No Shop-Script app installed');
        }

        $info = new cashShopImportProcessDto($this->processId);
        $info->period = waRequest::request('period', 'all', waRequest::TYPE_STRING_TRIM);
        $info->periodAfter = waRequest::request('period_after', null, waRequest::TYPE_STRING_TRIM);
        if ($info->period !== 'all' && empty($info->periodAfter)) {
            throw new kmwaRuntimeException('Invalid import timeframe start date');
        }
        $info->periodAfter = new DateTime($info->periodAfter);

        $info->totalOrders = $shopIntegration->countOrdersToProcess(
            $info->period === 'all' ? null : $info->periodAfter
        );

        $account = cash()->getEntityRepository(cashAccount::class)->findAllActiveForContact();
        if (!$account) {
            throw new kmwaRuntimeException('No Cash accounts');
        }
        /** @var cashAccount $account */
        $account = reset($account);

        $categories = cash()->getEntityRepository(cashCategory::class)->findAllIncomeForContact();
        if (!$categories) {
            throw new kmwaRuntimeException('No Cash categories');
        }

        $shopIntegration->getSettings()->setFirstTime(false)->saveFirstTime();
        $shopIntegration->getSettings()->setEnabled(true)->save();

        $this->data['info'] = $info;

        cash()->getLogger()->debug($this->_data, 'shop/import');
        cash()->getLogger()->debug('INIT ok', 'shop/import');
    }

    /**
     * @inheritDoc
     */
    protected function isDone()
    {
        return $this->data['info']->done;
    }

    /**
     * @return bool
     * @throws kmwaRuntimeException
     * @throws waDbException
     * @throws waException
     */
    protected function step()
    {
        cash()->getLogger()->debug('==== STEP ====', 'shop/import');
        $shopIntegration = new cashShopIntegration();
        if (!$shopIntegration->shopExists()) {
            throw new kmwaRuntimeException('No shop app');
        }

        $shopOrderModel = new shopOrderModel();
        $ordersSql = <<<SQL
select id from shop_order
where paid_date is not null
%s
order by id
limit i:start, i:chunk
SQL;
        $orders = $shopOrderModel->query(
            sprintf($ordersSql, $this->data['info']->period === 'all' ? '' : ' and create_datetime >= s:start_date'),
            [
                'start' => $this->data['info']->passedOrders,
                'chunk' => self::ORDERS_TO_PROCEED,
                'start_date' => $this->data['info']->periodAfter->format('Y-m-d 00:00:00'),
            ]
        )->fetchAll();
        $orders = array_column($orders, 'id');

        if (!$orders) {
            $this->data['info']->done = true;

            return true;
        }

        foreach ($orders as $orderId) {
            try {
                $dto = new cashShopCreateTransactionDto(['order_id' => $orderId]);
                if (!$dto->order->paid_date) {
                    throw new Exception(
                        sprintf('No paid date in order %s. Cant create transaction!', $dto->params['order_id'])
                    );
                }
                
                $shopIntegration->getTransactionFactory()->createIncomeTransaction($dto);
                $shopIntegration->saveTransactions($dto);

                if ($dto->mainTransaction) {
                    $this->data['info']->incomeTransactions++;
                }
                if ($dto->purchaseTransaction) {
                    $this->data['info']->expenseTransactions++;
                }
                if ($dto->shippingTransaction) {
                    $this->data['info']->expenseTransactions++;
                }
                if ($dto->taxTransaction) {
                    $this->data['info']->expenseTransactions++;
                }

            } catch (Exception $ex) {
                cash()->getLogger()->error(sprintf('Error on shop import order %s', $orderId), $ex, 'shop/import');
            } finally {
                $this->data['info']->passedOrders++;
            }
        }

        $this->data['info']->done = $this->data['info']->passedOrders >= $this->data['info']->totalOrders;

        if ($this->remaining_exec_time < $this->max_exec_time / 6) {
            return false;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    protected function info()
    {
        echo json_encode($this->data['info']);
    }

    /**
     * @inheritDoc
     */
    protected function finish($filename)
    {
        echo json_encode($this->data['info'], JSON_UNESCAPED_UNICODE);

        wa()->getStorage()->set(cashShopIntegration::SESSION_SSIMPORT, [
            'incomeTransactions' => $this->data['info']->incomeTransactions,
            'expenseTransactions' => $this->data['info']->expenseTransactions,
        ]);

        return true;
    }
}
