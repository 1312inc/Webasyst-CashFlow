<?php

/**
 * Class cashTransactionBulkCompleteMethod
 */
class cashTransactionBulkCompleteMethod extends cashApiAbstractMethod
{
    private const MAX_IDS = 500;

    protected $method = [self::METHOD_POST];

    /**
     * @return cashApiResponseInterface
     * @throws waAPIException
     * @throws waException
     */
    public function run(): cashApiResponseInterface
    {
        /** @var cashApiTransactionBulkCompleteRequest $request */
        $request = $this->fillRequestWithParams(new cashApiTransactionBulkCompleteRequest());

        if (count($request->ids) > self::MAX_IDS) {
            return new cashApiErrorResponse(sprintf_wp('Too many transactions to complete. Max %d', self::MAX_IDS));
        }

        $result = (new cashApiTransactionBulkCompleteHandler())->handle($request);

        if ($result) {
            return new cashApiResponse();
        }

        return new cashApiErrorResponse('Error on complete. See logs.');
    }
}
