<?php

/**
 * Class cashImportAction
 */
class cashImportAction extends cashViewAction
{
    public function preExecute()
    {
        if (wa()->whichUI() === '2.0') {
            $this->setLayout(new cashStaticLayout());
        }

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
