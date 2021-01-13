<?php

/**
 * Class cashContactsProfileTabListener
 */
class cashContactsProfileTabListener extends waEventHandler
{
    /**
     * @param $params
     *
     * @return mixed|void|null\
     */
    public function execute(&$params)
    {
        if ($this->isOldContacts()) {
            return;
        }

        $contact_id = $params;

        try {
            $old_app = wa()->getApp();
            wa(cashConfig::APP_ID, 1);

            if (!cash()->getContactRights()->hasAccessToApp(wa()->getUser())) {
                throw new waRightsException();
            }

            $backend_url = cash()->getBackendUrl(true);

            $result = [];

            $result[] = [
                'id' => 'cashapp',
                'title' => _w('Cash Flow'),
                'url' => sprintf('%scash/?module=crm&contact=%s&external=1', $backend_url, $contact_id),
            ];
        } catch (waException $ex) {
            waLog::log(sprintf('Something went wrong: %s. %s', $ex->getMessage(), $ex->getTraceAsString()));
        } finally {
            wa($old_app, 1);
        }

        return ifempty($result, null);
    }

    /**
     * @return bool
     */
    protected function isOldContacts()
    {
        $is_old_contacts = waRequest::request('module', '', 'string') == 'contacts'
            && waRequest::request('action', '', 'string') == 'info'
            && wa()->appExists('contacts');
        if ($is_old_contacts) {
            $is_old_contacts = version_compare(wa()->getVersion('contacts'), '1.2.0') < 0;
        }

        return $is_old_contacts;
    }
}
