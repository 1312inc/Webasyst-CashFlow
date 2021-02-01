<?php

/**
 * Class cashTransactionGetMethod
 */
class cashTransactionGetMethod extends cashApiAbstractMethod
{
    protected $method = self::METHOD_GET;

    /**
     * @var cashUserRepository
     */
    private $userRepository;

    public function __construct()
    {
        parent::__construct();

        $this->userRepository = new cashUserRepository();
    }

    /**
     * @return cashApiTransactionGetResponse
     * @throws kmwaForbiddenException
     * @throws kmwaNotFoundException
     * @throws waAPIException
     * @throws waException
     */
    public function run(): cashApiResponseInterface
    {
        /** @var cashApiTransactionGetRequest $request */
        $request = $this->fillRequestWithParams(new cashApiTransactionGetRequest());

        $transactions = (new cashApiTransactionGetHandler())->handle($request);

        /** @var cashApiTransactionResponseDto $transaction */
        foreach ($transactions as $transaction) {
            $transaction->addCreateContactData($this->userRepository->getUser($transaction->create_contact_id));
            if (!$transaction->contractor_contact_id) {
                continue;
            }

            $transaction->addContractorContactData($this->userRepository->getUser($transaction->contractor_contact_id));
        }

        return new cashApiTransactionGetResponse($transactions);
    }
}
