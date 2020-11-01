<?php

/**
 * Class cashImportAction
 */
class cashImportAction extends cashViewAction
{
    public function preExecute()
    {
        $this->setLayout(new cashStaticLayout());

        parent::preExecute();
    }

    /**
     * @param null|array $params
     *
     * @return mixed
     */
    public function runAction($params = null)
    {
    }
}
