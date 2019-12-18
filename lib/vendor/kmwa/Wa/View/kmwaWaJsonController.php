<?php

/**
 * Class kmwaWaJsonController
 */
class kmwaWaJsonController extends waJsonController
{
    use kmwaWaViewTrait;

    /**
     * @param null $params
     */
    public function run($params = null)
    {
        try {
            $this->preExecute();
            $this->execute();
            $this->afterExecute();
        } catch (waException $ex) {
            $this->errors = $ex->getMessage();
        }

        $this->display();
    }
}
