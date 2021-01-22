<?php

class cashTransactionGetBadgeCountMethod extends cashApiAbstractMethod
{
    protected $method = self::METHOD_GET;

    /**
     * @return cashApiTransactionGetBadgeCountResponse
     * @throws waAPIException
     * @throws waException
     */
    public function run(): cashApiResponseInterface
    {
        /** @var cashApiTransactionGetBadgeCountRequest $request */
        $request = $this->fillRequestWithParams(new cashApiTransactionGetBadgeCountRequest());
        $request->date = DateTimeImmutable::createFromFormat('Y-m-d', $request->date);

        return new cashApiTransactionGetBadgeCountResponse(
            (new cashApiTransactionGetBadgeCountHandler())->handle($request)
        );
    }
}
