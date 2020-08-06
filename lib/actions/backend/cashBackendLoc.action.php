<?php

/**
 * A list of localized strings to use in JS.
 */
class cashBackendLocAction extends cashViewAction
{
    /**
     * @param null $params
     *
     * @return mixed|void
     */
    public function runAction($params = null)
    {
        $strings = [];

        // Application locale strings
        $translates = [
            _w('The transaction will be permanently deleted. Are you sure?'),
            _w('DANGER: This will permanently delete the entire account and ALL TRANSACTIONS without the ability to restore. Are you sure?'),
            _w('DANGER: This will permanently delete the entire category and ALL TRANSACTIONS without the ability to restore. Are you sure?'),
            _w('Save all'),
            _w('Save'),
            _w('Delete transactions?'),
            _w('Delete transaction?'),
            _w('Clear import history (don’t worry, imported transactions will not be affected)?'),
            _w('New income category: '),
            _w('New expense category: '),
            _w('Enter format'),
            _w('transactions'),
            _w('Start repeat'),
            _w('Date'),
            _w('Save %d transactions'),
            _w('Delete %s transactions'),
            _w('New contact will be created'),
            _w('Search for existing contact or enter any new contact name.'),
            _w('Skip rows with this value'),
            _w('This will flush all Shop-Script import settings so you can re-start the import from scratch. All existing transactions will remain as is unless you delete them manually. Re-start the integration?'),
            _w('Import %d paid orders'),
        ];

        $this->view->assign(
            'strings',
            $strings ?: new stdClass()
        ); // stdClass is used to show {} instead of [] when there's no strings

        $this->getResponse()->addHeader('Content-Type', 'text/javascript; charset=utf-8');
    }
}
