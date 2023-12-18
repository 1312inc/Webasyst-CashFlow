<?php

class cashTinkoffPluginBackendGetStatementController extends waJsonController
{
    public function execute()
    {
        $plugin = new cashTinkoffPlugin(['id' => 'tinkoff', 'profile' => 1]);
        $response = $plugin->getStatement();

        $this->response = $this->addTransactions($response);
    }

    private function addTransactions($response)
    {
        if (!empty($response['operations'])) {
            $transaction_model = cash()->getModel(cashTransaction::class);
            $account_model = cash()->getModel(cashAccount::class);
            $account = $account_model->getById(1);
            $create_contact_id = wa()->getUser()->getId();
            foreach ($response['operations'] as &$_operation) {
                $now = date('Y-m-d H:i:s');
                $is_credit = ('Credit' == ifset($_operation, 'typeOfOperation', 'Credit'));
                $date_operation = strtotime(ifset($_operation, 'operationDate', $now));
                $_operation = [
                    'date'              => date('Y-m-d', $date_operation),
                    'datetime'          => date('Y-m-d H:i:s', $date_operation),
                    'account_id'        => ifset($account, 'id', 0),
                    'category_id'       => 1,
                    'amount'            => ($is_credit ? 1 : -1) * ifset($_operation, 'operationAmount', 0),
                    'description'       => ifset($_operation, 'payPurpose', ''),
                    'create_contact_id' => $create_contact_id,
                    'create_datetime'   => $now,
                    'external_source'   => 'api_tinkoff',
                    'external_hash'     => ifset($_operation, 'operationId', null)
                ];
                $transaction_model->insert($_operation);
            }
        }

        return $response;
    }
}
