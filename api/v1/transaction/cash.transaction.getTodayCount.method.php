<?php

class cashTransactionGetTodayCountMethod extends cashApiAbstractMethod
{
    protected $method = self::METHOD_GET;

    /**
     * @return cashApiTransactionGetTodayCountResponse
     * @throws waAPIException
     * @throws waException
     */
    public function run(): cashApiResponseInterface
    {
        /** @var cashApiTransactionGetTodayCountRequest $request */
        $request = $this->fillRequestWithParams(new cashApiTransactionGetTodayCountRequest());
        $request->today = DateTimeImmutable::createFromFormat('Y-m-d', $request->today);
        if (!$request->today) {
            return new cashApiErrorResponse("Wrong date format. Expected 'Y-m-d'");
        }

        return new cashApiTransactionGetTodayCountResponse(
            (new cashApiTransactionGetTodayCountHandler())->handle($request)
        );
    }
}
