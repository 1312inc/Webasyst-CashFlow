<?php

/**
 * Class kmwaWaViewAction
 */
abstract class kmwaWaViewAction extends waViewAction
{
    use kmwaWaViewTrait;

    /**
     * @param null|array $params
     *
     * @return mixed
     */
    abstract public function runAction($params = null);

    /**
     * @return array
     */
    abstract protected function getDefaultViewVars();

    /**
     * @param null $params
     */
    public function execute($params = null)
    {
        try {
            foreach ($this->getDefaultViewVars() as $key => $value) {
                $this->view->smarty->assignGlobal($key, $value);
            }

            $this->runAction($params);
        } catch (Exception $ex) {
            $this->view->assign(
                'error',
                [
                    'code'    => $ex->getCode(),
                    'message' => $ex->getMessage(),
                ]
            );

            $this->setTemplate(wa()->getAppPath('templates/include/error.html'));
        }
    }
}
