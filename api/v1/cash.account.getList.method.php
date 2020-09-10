<?php

/**
 * Class cashAccountGetListMethod
 */
class cashAccountGetListMethod extends cashApiAbstractMethod
{
    protected $method = self::METHOD_GET;

    public function run()
    {
        return (new cashApiAccountGetListHandler())->handle(null);
    }
}
