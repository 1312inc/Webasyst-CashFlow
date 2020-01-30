<?php

/**
 * Class cashImportCsvProcessController
 */
class cashImportCsvProcessController extends waLongActionController
{
    /**
     * @inheritDoc
     */
    protected function init()
    {
        $settings = waRequest::post('import', [], waRequest::TYPE_ARRAY);
        $this->data['settings'] = $settings;
    }

    /**
     * @inheritDoc
     */
    protected function isDone()
    {
        // TODO: Implement isDone() method.
    }

    /**
     * @inheritDoc
     */
    protected function step()
    {
        // TODO: Implement step() method.
    }

    /**
     * @inheritDoc
     */
    protected function finish($filename)
    {
        // TODO: Implement finish() method.
    }

    /**
     * @inheritDoc
     */
    protected function info()
    {
        // TODO: Implement info() method.
    }
}
