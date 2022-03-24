<?php

class cashShopUtils
{
    public function loadPaymentMethods(): array
    {
        $plugins = shopPayment::getList();

        $model = new shopPluginModel();
        $instances = $model->listPlugins(shopPluginModel::TYPE_PAYMENT, ['all' => true]);
        $data = [];
        foreach ($instances as &$instance) {
            $instance['installed'] = isset($plugins[$instance['plugin']]);
            if ($instance['installed']) {
                $data[$instance['id']] = [
                    'enable' => (bool) $instance['status'],
                    'name' => $instance['name'],
                ];
            }
            unset($instance);
        }

        return $data;
    }
}
