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
        $request->today = DateTimeImmutable::createFromFormat('Y-m-d', $request->today);
        if (!$request->today) {
            return new cashApiErrorResponse("Wrong date format. Expected 'Y-m-d'");
        }

        return new cashApiTransactionGetBadgeCountResponse(
            (new cashApiTransactionGetBadgeCountHandler())->handle($request)
        );
    }
}
