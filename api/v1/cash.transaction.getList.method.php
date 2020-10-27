<?php

/**
 * Class cashTransactionGetListMethod
 *
 * Request:
 * <code>
 * {
 *      "from": "2018-03-01",
 *      "to": "2020-04-01",
 *      "limit": "100",
 *      "start": "0",
 *      "filter": "category/2"
 * }
 * </code>
 *
 * from, to - date limits,
 * limit - how many transactions to get,
 * start - starting from,
 * filter - hash to filter transaction list, can be "category/X", "account/X", "contractor/X", "currency/XXX"
 *
 * all params are required
 *
 * Response:
 * <code>
 * [
 *  {
 *      "id": 2,
 *      "date": "2019-11-22",
 *      "datetime": "2019-11-22 00:00:00",
 *      "amount": 192000,
 *      "amountShorten": "192K",
 *      "balance": null,
 *      "balanceShorten": "0",
 *      "description": "Взяли кредит в банке",
 *      "repeating_id": null,
 *      "create_contact_id": 1,
 *      "create_datetime": "2020-10-22 20:33:34",
 *      "update_datetime": null,
 *      "category_id": 2,
 *      "account_id": 2,
 *      "planned": false,
 *      "is_archived": false,
 *      "external_hash": null,
 *      "external_source": null,
 *      "external_data": null,
 *      "contractor_contact_id": null
 *  }
 * ]
 * </code>
 */
class cashTransactionGetListMethod extends cashApiAbstractMethod
{
    protected $method = self::METHOD_GET;

    /**
     * @return cashApiTransactionGetListResponse
     * @throws kmwaForbiddenException
     * @throws waAPIException
     * @throws waException
     */
    public function run(): cashApiResponseInterface
    {
        /** @var cashApiTransactionGetListRequest $request */
        $request = $this->fillRequestWithParams(new cashApiTransactionGetListRequest());
        $request->offset = (int) $request->offset;
        if ($request->limit > 500) {
            $request->limit = 500;
        }

        $transactions = (new cashApiTransactionGetListHandler())->handle($request);

        return new cashApiTransactionGetListResponse(
            $transactions['data'],
            $transactions['total'],
            (int) $request->offset,
            (int) $request->limit
        );
    }
}
