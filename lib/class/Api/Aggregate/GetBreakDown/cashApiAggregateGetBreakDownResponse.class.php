<?php

/**
 * Class cashApiAggregateGetBreakDownResponse
 */
final class cashApiAggregateGetBreakDownResponse extends cashApiAbstractResponse
{
    /**
     * cashApiAggregateGetBreakDownResponse constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct(200);

        $response = [];
        foreach ($data as $graphDatum) {
            $key = sprintf('%s/%s', $graphDatum['type'], $graphDatum['currency']);
            if (!isset($response[$key])) {
                $response[$key] = new cashApiAggregateGetBreakDownDto($graphDatum['currency'], $graphDatum['type'], []);
            }

            $dataInfo = new cashApiAggregateGetBreakDownDataDto(
                $graphDatum['amount'],
                $graphDatum['detailed']
            );
            $response[$key]->data[] = $dataInfo;
            $response[$key]->totalAmount += abs($dataInfo->amount);
        }

        ksort($response);
        $this->response = array_values($response);
    }
}
