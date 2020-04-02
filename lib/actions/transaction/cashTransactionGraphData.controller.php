<?php

/**
 * Class cashTransactionGraphDataController
 */
class cashTransactionGraphDataController extends cashTransactionPageAction
{
    /**
     * @var array
     */
    private $errors = [];

    /**
     * @var array
     */
    private $response = [];

    /**
     * @throws Exception
     */
    public function runAction($params = null)
    {
        try {
            $graphService = new cashGraphService();
            $graphData = $graphService->createDto(
                $this->startDate,
                $this->endDate,
                $this->filterDto
            );

            switch ($this->filterDto->type) {
                case cashTransactionPageFilterDto::FILTER_ACCOUNT:
                    $graphService->fillColumnCategoriesDataForAccounts($graphData);
                    $graphService->fillBalanceDataForAccounts($graphData);
                    break;

                case cashTransactionPageFilterDto::FILTER_CATEGORY:
                    $graphService->fillColumnCategoriesDataForCategories($graphData);
//                    $graphService->fillBalanceDataForCategories($graphData);
                    break;

                case cashTransactionPageFilterDto::FILTER_IMPORT:
                    $graphService->fillColumnCategoriesDataForImport($graphData);
                    $graphService->fillBalanceDataForImport($graphData);
                    break;
            }

            $this->response = $graphData;
        } catch (Exception $exception) {
            $this->errors[] = $exception->getMessage();
        }

        $this->display();
    }

    /**
     * @param bool $clear_assign
     *
     * @return string|void
     */
    public function display($clear_assign = true)
    {
        if (waRequest::isXMLHttpRequest()) {
            $this->getResponse()->addHeader('Content-Type', 'application/json');
        }
        $this->getResponse()->sendHeaders();
        if (!$this->errors) {
            echo waUtils::jsonEncode(['status' => 'ok', 'data' => $this->response]);
        } else {
            echo waUtils::jsonEncode(['status' => 'fail', 'errors' => $this->errors]);
        }
    }
}
