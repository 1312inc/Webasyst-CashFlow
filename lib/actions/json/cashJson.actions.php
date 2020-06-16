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
            $today = (new DateTime())->setTime(0, 0);
            $date = (new DateTime($date))->setTime(0, 0);

            $diff = $today->diff($date);
            switch (true) {
                case $diff->d == 0:
                    $this->response = _w('Today');
                    break;

                case $diff->d == 1 && $diff->invert:
                    $this->response = _w('Yesterday');
                    break;

                case $diff->d == 1 && !$diff->invert:
                    $this->response = _w('Tomorrow');
                    break;

                case $diff->invert:
                    $this->response = sprintf_wp('%d days ago', $diff->d);
                    break;

                case !$diff->invert:
                    $this->response = sprintf_wp('In %d days', $diff->d);
                    break;

                default:
                    $this->response = _w('Any date in the past or in the future');
            }
        } catch (Exception $ex) {
            $this->response = _w('Any date in the past or in the future');
        }
    }
}
