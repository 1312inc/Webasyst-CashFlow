<?php

class cashTemplateDeleteController extends cashJsonController
{
    public function execute()
    {
        $template_id = waRequest::post('template_id', null, waRequest::TYPE_INT);

        if ($template_id > 0) {
            $oar_model = new cashTemplateModel();
            $oar_model->deleteById($template_id);
        }

        $this->response = 'ok';
    }
}
