<?php

class cashResetCli extends waCliController
{
    public function run($params = null)
    {
        (new cashDebugResetAction())->runAction();
    }
}
