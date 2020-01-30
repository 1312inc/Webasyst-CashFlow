<?php

/**
 * Class cashImportUploadAction
 */
class cashImportUploadAction extends cashViewAction
{
    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @param null $params
     */
    public function runAction($params = null)
    {
        $this->getStorage()->close();
        $files = waRequest::file('upload');

        $importService = new cashImportService();
        $responses = $importService->uploadFile($files, waRequest::request('upload', [], waRequest::TYPE_ARRAY));

        $this->view->assign(
            [
                'errors' => $importService->getErrors(),
                'responses' => $responses,
            ]
        );
    }
}
