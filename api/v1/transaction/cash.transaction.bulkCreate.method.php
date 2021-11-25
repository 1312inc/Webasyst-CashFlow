<?php

final class cashTransactionBulkCreateMethod extends cashApiAbstractMethod
{
    private const MAX_PER_REQUEST = 13;

    protected $method = self::METHOD_POST;

    /**
     * @return cashApiAccountCreateResponse
     */
    public function run(): cashApiResponseInterface
    {
        $errors = [];
        $requests = [];
        $responses = [];
        $i = 0;
        foreach ($this->jsonParams as $data) {
            if ($i++ >= self::MAX_PER_REQUEST) {
                break;
            }

            $request = new cashApiTransactionCreateRequest();
            try {
                foreach (get_object_vars($request) as $prop => $value) {
                    if ($value !== null && empty($data[$prop])) {
                        throw new cashApiMissingParamException($prop);
                    }

                    if (isset($data[$prop])) {
                        $request->{$prop} = $data[$prop];
                    }
                }
                $requests[] = $request;
            } catch (cashApiMissingParamException $exception) {
                $errors[] = $exception->getMessage();
            }
        }

        try {
            foreach ($requests as $request) {
                if ($request->transfer_account_id && $request->category_id !== cashCategoryFactory::TRANSFER_CATEGORY_ID) {
                    return new cashApiErrorResponse(
                        'Transfer category may not be other than ' . cashCategoryFactory::TRANSFER_CATEGORY_ID
                    );
                }

                if ($request->category_id == cashCategoryFactory::TRANSFER_CATEGORY_ID && !$request->transfer_account_id) {
                    return new cashApiErrorResponse('Missing transfer information');
                }

                $response = (new cashApiTransactionCreateHandler())->handle($request);

                $responses = array_merge($responses, $response);
            }
        } catch (Exception $exception) {
            $errors[] = $exception->getMessage();
            cash()->getLogger()->error('Error on bulk transaction create', $exception);
        }

        cash()->getEventDispatcher()->dispatch(
            new cashEvent(cashEventStorage::API_TRANSACTION_BEFORE_RESPONSE, new ArrayIterator($responses))
        );

        return new cashApiTransactionBulkMoveResponse($responses);
    }
}
