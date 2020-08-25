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
     * @throws kmwaForbiddenException
     * @throws waException
     */
    protected function preExecute()
    {
        if (!cash()->getUser()->canImport()) {
            throw new kmwaForbiddenException();
        }
    }

    /**
     * @param null $params
     */
    public function runAction($params = null)
    {
        $this->getStorage()->close();
        $files = waRequest::file('upload');

        $errors = $responses = [];
        $importService = new cashImportService();
        try {
            $responses = $importService->uploadFile($files, waRequest::request('upload', [], waRequest::TYPE_ARRAY));
            $errors = $importService->getErrors();
        } catch (Exception $exception) {
            $errors[] = _w('Some error occurs: %s', $exception->getMessage());
            cash()->getLogger()->error('Error on import file upload', $exception);
        }

        $this->view->assign(
            [
                'errors' => $errors,
                'responses' => $responses,
            ]
        );
    }
}
