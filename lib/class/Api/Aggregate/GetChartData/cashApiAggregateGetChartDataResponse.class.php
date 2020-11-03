<?php

/**
 * Class cashApiAggregateGetChartDataResponse
 */
final class cashApiAggregateGetChartDataResponse extends cashApiAbstractResponse
{
    /**
     * cashApiCategoryCreateResponse constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct(200);

        $response = [];

        foreach ($data as $graphDatum) {
            if (!isset($response[$graphDatum['currency']])) {
                $response[$graphDatum['currency']] = [];
            }

            $response[$graphDatum['currency']][] = new cashApiAggregateGetChartDataDto(
                $graphDatum['groupkey'],
                abs($graphDatum['incomeAmount']),
                abs($graphDatum['expenseAmount']),
                $graphDatum['balance']
            );
        }

        foreach ($response as $currency => $dtos) {
            $this->response[] = [
                'currency' => $currency,
                'data' => $dtos,
            ];
        }
    }
}
