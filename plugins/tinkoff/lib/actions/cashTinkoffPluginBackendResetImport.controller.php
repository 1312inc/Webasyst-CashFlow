<?php

class cashTinkoffPluginBackendResetImportController extends waJsonController
{
    public function execute()
    {
        if (waRequest::getMethod() === 'get') {
            wa()->getStorage()->del('cash.tinkoff_import_reset_code');
            $code = 'YES '.substr(md5(mt_rand()), 0, 3);
            wa()->getStorage()->set('cash.tinkoff_import_reset_code', $code);

            $view = wa()->getView();
            $view->assign('code', $code);
            $this->response = $view->fetch('plugins/tinkoff/templates/actions/backend/BackendResetImportDialog.html');
        } else {
            /** reset import */
            $profile_id = waRequest::post('profile_id', null, waRequest::TYPE_INT);
            if (empty($profile_id)) {
                $this->setError(_wp('Не указан ID профиля'));
            }
            $code = wa()->getStorage()->get('cash.tinkoff_import_reset_code');
            if ($code === waRequest::post('code', '')) {
                wa()->getStorage()->del('cash.tinkoff_import_reset_code');

                /** @var cashTinkoffPlugin $plugin */
                $plugin = wa()->getPlugin('tinkoff')->setCashProfile($profile_id);
                $plugin->saveSettings(['current_profile_id' => $profile_id]);
                $profile = $plugin->getProfile($profile_id);
                $plugin->saveProfile($profile_id, [
                    'update_time' => '',
                    'import_id' => 0,
                    'first_update' => true,
                    'cash_account' => 0,
                    'last_update_time' => ''
                ]);
                $cash_account = (int) ifempty($profile, 'cash_account', 0);
                $source = $plugin->getExternalSource();
                $trs = cash()->getModel(cashTransaction::class)
                    ->select('id, import_id')
                    ->where('account_id = ?', $cash_account)
                    ->where('external_source = ?', $source)
                    ->fetchAll();
                $transaction_ids = array_column($trs, 'id');
                $import_ids = array_unique(array_column($trs, 'import_id'));
                if ($transaction_ids) {
                    cash()->getModel('cashTransactionData')->deleteByField('transaction_id', $transaction_ids);
                    cash()->getModel(cashTransaction::class)->deleteById($transaction_ids);
                    if ($import_ids) {
                        cash()->getModel(cashImport::class)->deleteById($import_ids);
                    }
                }
                $profile_run_data = (array) $this->getStorage()->read('profile_run_data');
                unset($profile_run_data[$profile_id]);
                $this->getStorage()->write('profile_run_data', $profile_run_data);
            } else {
                $this->setError(_wp('Invalid code'));
            }
        }
    }
}
