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
            'The transaction will be permanently deleted. Are you sure?',
            'DANGER: This will permanently delete the entire account and ALL TRANSACTIONS without the ability to restore. Are you sure?',
            'DANGER: This will permanently delete the entire category and ALL TRANSACTIONS without the ability to restore. Are you sure?',
            'Save all',
            'Save',
            'Delete transactions?',
            'Delete transaction?',
            'Clear import history (don’t worry, imported transactions will not be affected)?',
            'New income category: ',
            'New expense category: ',
            'Enter format',
            'transactions',
            'Start repeat',
            'Date',
            'Save %d transactions',
            'Delete %s transactions',
            'New contact will be created',
            'Search for existing contact or enter any new contact name.',
            'Skip rows with this value',
            'Import %d paid orders',
            'Expense',
            'Income',
            'transactions',
        ];
        foreach ($translates as $s) {
            $strings[$s] = _w($s);
        }

        foreach ($translates as $s) {
            $strings[$s] = _w($s);
        }

        $this->view->assign(
            'strings',
            $strings ?: new stdClass()
        ); // stdClass is used to show {} instead of [] when there's no strings

        $this->getResponse()->addHeader('Content-Type', 'text/javascript; charset=utf-8');
    }
}
