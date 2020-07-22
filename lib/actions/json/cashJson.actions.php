<?php

/**
 * Class cashBackendJsonActions
 */
class cashJsonActions extends kmwaWaJsonActions
{
    /**
     * @throws Exception
     */
    public function whichDateIsAction()
    {
        try {
            $date = waRequest::post('date');
            $today = new DateTime('midnight');
            $date = (new DateTime($date))->modify('midnight');

            $diff = $today->diff($date);
            switch (true) {
                case $diff->days === 0:
                    $this->response = _w('Today');
                    break;

                case $diff->days === 1 && $diff->invert:
                    $this->response = _w('Yesterday');
                    break;

                case $diff->days === 1 && !$diff->invert:
                    $this->response = _w('Tomorrow');
                    break;

                case $diff->invert:
                    $this->response = sprintf_wp('%d days ago', $diff->days);
                    break;

                case !$diff->invert:
                    $this->response = sprintf_wp('In %d days', $diff->days);
                    break;

                default:
                    $this->response = _w('Any date in the past or in the future');
            }
        } catch (Exception $ex) {
            $this->response = _w('Any date in the past or in the future');
        }
    }

    public function contactAutocompleteAction()
    {
        $this->response = [];
        try {
            $term = waRequest::get('term', '', waRequest::TYPE_STRING_TRIM);
            if ($term) {
                $this->response = (new cashAutocomplete())->findContacts($term, 10);
            }
        } catch (Exception $exception) {
            // tsss.. silence
        }
    }
}
