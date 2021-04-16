<?php

class cashPluginsActions extends waPluginsActions
{
    protected $plugins_hash = '#';
    protected $is_ajax = false;
    protected $shadowed = true;

    public function defaultAction()
    {
        if (!$this->getUser()->isAdmin($this->getApp())) {
            throw new waRightsException(_w('Access denied'));
        }
        $this->getResponse()->setTitle(_w('Plugin settings page'));

        if (wa()->whichUI($this->getAppId()) == '2.0') {
            $this->setLayout(new cashStaticLayout());
        } else {
            $this->is_ajax = true;
            $this->setLayout(new cashDefaultLayout());
        }

//        if (!waRequest::isXMLHttpRequest()) {
//            $this->setLayout(new crmDefaultLayout());
//        }

        parent::defaultAction();
    }
}
