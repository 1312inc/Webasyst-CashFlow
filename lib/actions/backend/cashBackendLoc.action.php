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
        ];
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
