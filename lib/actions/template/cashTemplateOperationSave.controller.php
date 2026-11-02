<?php

class cashTemplateOperationSaveController extends cashJsonController
{
    public function execute()
    {
        $data_templates = waRequest::post('data', [], waRequest::TYPE_ARRAY);

        if (empty($data_templates)) {
            return null;
        }

        $templates_insert = [];
        $templates_update = [];
        $contact_id = wa()->getUser()->getId();
        $categories = cash()->getEntityRepository(cashCategory::class)->findAllActiveForContact();
        foreach ($data_templates as $_template_id => $_template) {
            /** @var cashCategory $category */
            $category = ifset($categories, $_template['category_id'], []);
            if ($category) {
                $type = $category->getType();
            } else {
                $type = cashCategory::TYPE_INCOME;
            }
            if ($type === cashCategory::TYPE_EXPENSE) {
                $_template['amount'] = -abs((float) $_template['amount']);
            } elseif ($type === cashCategory::TYPE_INCOME) {
                $_template['amount'] = abs((float) $_template['amount']);
            }
            $_template += [
                'contact_id' => $contact_id,
                'type' => $type,
                'sort' => 0
            ];

            if ($_template_id < 1) {
                $templates_insert[] = $_template;
            } else {
                $_template['id'] = (int) $_template_id;
                $templates_update[$_template_id] = $_template;
            }
        }

        $ct_model = new cashTemplateModel();

        if ($templates_insert) {
            $ct_model->multipleInsert($templates_insert);
        }
        if ($templates_update) {
            foreach ($templates_update as $id => $_data) {
                $ct_model->updateById($id, $_data);
            }
        }

        $this->response = 'ok';
    }
}
