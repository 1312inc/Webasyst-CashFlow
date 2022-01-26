<?php

class cashShopUtils
{
    public function loadPaymentMethods(): array
    {
        $plugins = shopPayment::getList();

        $model = new shopPluginModel();
        $instances = $model->listPlugins(shopPluginModel::TYPE_PAYMENT, array('all' => true,));
        $data = [];
        foreach ($instances as &$instance) {
            $instance['installed'] = isset($plugins[$instance['plugin']]);
            if ($instance['installed']) {
                $data[$instance['plugin']] = $instance['info']['name'];
            }
            unset($instance);
        }

        return $data;
    }
}
