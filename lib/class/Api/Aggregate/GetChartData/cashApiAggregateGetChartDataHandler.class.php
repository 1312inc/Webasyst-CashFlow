<?php

/**
 * Class cashApiAggregateGetChartDataHandler
 */
class cashApiAggregateGetChartDataHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiAggregateGetChartDataRequest $request
     *
     * @return cashApiAggregateGetChartDataDto[]
     * @throws kmwaForbiddenException
     * @throws kmwaRuntimeException
     * @throws waException
     */
    public function handle($request)
    {
        $paramsDto = new cashAggregateChartDataFilterParamsDto(
            wa()->getUser(),
            $request->account_id,
            $request->category_id,
            $request->from,
            $request->to,
            $request->group_by,
            $request->currency->getCode()
        );

        $graphService = new cashGraphService();
        $graphData = $graphService->getAggregateChartData($paramsDto);

        $response = [];
        foreach ($graphData as $graphDatum) {
            $response[] = new cashApiAggregateGetChartDataDto(
                $graphDatum['groupkey'],
                $graphDatum['incomeAmount'],
                $graphDatum['expenseAmount'],
                0
            );
        }

//
//        switch ($this->filterDto->type) {
//            case cashTransactionPageFilterDto::FILTER_ACCOUNT:
//                $graphService->fillColumnCategoriesDataForAccounts($graphData);
//                $graphService->fillBalanceDataForAccounts($graphData);
//                break;
//
//            case cashTransactionPageFilterDto::FILTER_CATEGORY:
//                $graphService->fillColumnCategoriesDataForCategories($graphData);
////                    $graphService->fillBalanceDataForCategories($graphData);
//                break;
//
//            case cashTransactionPageFilterDto::FILTER_IMPORT:
//                $graphService->fillColumnCategoriesDataForImport($graphData);
////                    $graphService->fillBalanceDataForImport($graphData);
//                break;
//        }

        return $response;
    }
}
