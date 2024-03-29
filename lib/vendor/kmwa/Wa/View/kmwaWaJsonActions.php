<?php

/**
 * Class kmwaWaJsonActions
 */
class kmwaWaJsonActions extends waJsonActions
{
    use kmwaWaViewTrait;

    /**
     * @param $action
     */
    public function execute($action)
    {
        try {
            parent::execute($action);
        } catch (waException $ex) {
            $this->errors[] = $ex->getMessage();
        }
    }

    /**
     * @throws waException
     */
    public function defaultAction()
    {
        throw new waException('Unknown action.');
    }

    /**
     * @param string $message
     * @param array  $data
     */
    public function setError($message, $data = [])
    {
        $this->errors[] = [$message, $data];
    }
}
