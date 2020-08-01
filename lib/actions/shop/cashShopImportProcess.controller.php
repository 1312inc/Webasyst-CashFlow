<?php

/**
 * Class cashShopImportProcessController
 *
 * @todo move process to service
 * @property cashShopImportProcessDto  data['info']
 */
class cashShopImportProcessController extends waLongActionController
{
    const ORDERS_TO_PROCEED = 30;

    public function execute()
    {
        try {
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
            throw new kmwaRuntimeException('No shop app');
        }

        $shopWelcome = new cashShopWelcome($shopIntegration);

        $info = new cashShopImportProcessDto($this->processId);
        $info->totalOrders = $shopWelcome->countOrdersToProcess();

        $account = cash()->getEntityRepository(cashAccount::class)->findAllActive();
        if (!$account) {
            throw new kmwaRuntimeException('No Cash accounts');
        }
        /** @var cashAccount $account */
        $account = reset($account);

        $categories = cash()->getEntityRepository(cashCategory::class)->findAllIncome();
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
order by id 
limit i:start, i:chunk
SQL;
        $orders = $shopOrderModel->query(
            $ordersSql,
            ['start' => $this->data['info']->passedOrders, 'chunk' => self::ORDERS_TO_PROCEED]
        )->fetchAll();
        $orders = array_column($orders, 'id');

        if (!$orders) {
            $this->data['info']->done = true;

            return true;
        }

        $factory = new cashShopTransactionFactory($shopIntegration->getSettings());

        foreach ($orders as $orderId) {
            try {
                $dto = new cashShopCreateTransactionDto(
                    ['order_id' => $orderId, 'action_id' => '', 'before_state_id' => '', 'after_state_id' => '']
                );
                $factory->createTransactions($dto, cashShopTransactionFactory::INCOME);
                $shopIntegration->saveTransactions($dto);
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

        return true;
    }
}
