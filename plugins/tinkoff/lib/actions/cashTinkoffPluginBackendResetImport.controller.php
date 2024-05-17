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
                $plugin->saveProfile($profile_id, [
                    'update_time' => '',
                    'first_update' => true,
                    'last_update_time' => ''
                ]);
                $source = $plugin->getExternalSource();
                $transaction_ids = array_column(cash()->getModel(cashTransaction::class)
                    ->select('id')
                    ->where('external_source = ?', $source)
                    ->where('external_hash IS NOT NULL')
                    ->fetchAll(),
                    'id'
                );
                if ($transaction_ids) {
                    cash()->getModel('cashTransactionData')->deleteByField('transaction_id', $transaction_ids);
                }
                cash()->getModel(cashTransaction::class)->deleteBySource($source);
                $profile_run_data = (array) $this->getStorage()->read('profile_run_data');
                unset($profile_run_data[$profile_id]);
                $this->getStorage()->write('profile_run_data', $profile_run_data);
            } else {
                $this->setError(_wp('Invalid code'));
            }
        }
    }
}
