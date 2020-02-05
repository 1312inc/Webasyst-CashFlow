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
     * @param bool $clear_assign
     *
     * @return string|void
     */
    public function display($clear_assign = true)
    {
        try {
            return parent::display($clear_assign);
        } catch (Exception $ex) {
            $this->view->assign(
                'error',
                [
                    'code' => $ex->getCode(),
                    'message' => $ex->getMessage(),
                ]
            );

            $result = $this->view->fetch(wa()->getAppPath('templates/include/error.html'));
            if ($clear_assign) {
                $this->view->clearAllAssign();
            }

            return $result;
        }
    }

    /**
     * @param null $params
     */
    public function execute($params = null)
    {
        foreach ($this->getDefaultViewVars() as $key => $value) {
            $this->view->smarty->assignGlobal($key, $value);
        }

        $this->runAction($params);
    }
}
