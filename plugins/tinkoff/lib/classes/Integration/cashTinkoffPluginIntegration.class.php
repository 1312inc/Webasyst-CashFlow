<?php

class cashTinkoffPluginIntegration
{
    private const BASE_URL = 'https://business.tinkoff.ru/openapi/api';

    private $waNet;

    public function __construct(cashTinkoffPluginToken $token)
    {
        $this->waNet = new waNet(
            [
                'format' => waNet::FORMAT_JSON,
                'request_format' => waNet::FORMAT_JSON,
                'authorization' => true,
                'auth_type' => 'Bearer',
                'auth_key' => $token->getValue(),
                'log' => waSystemConfig::isDebug(),
                'expected_http_code' => 200,
            ]
        );
    }

    /**
     * @return array<cashTinkoffPluginBankAccountResponseDto>|null
     */
    public function getBankAccounts(): ?array
    {
        cashTinkoffPlugin::debug('Get tinkoff bank accounts');

        try {
            $accounts = $this->waNet->query(self::BASE_URL . '/v3/bank-accounts');
        } catch (waException $exception) {
            $this->handleError($exception);

            return null;
        }

        cashTinkoffPlugin::debug(sprintf('Got response: %s', $this->waNet->getResponse(true)));

        $dtos = [];
        foreach ($accounts as $account) {
            $dtos[] = cashTinkoffPluginBankAccountResponseDto::fromArray($account);
        }

        return $dtos;
    }

    /**
     * @return cashTinkoffPluginBankStatementResponseDto
     */
    public function getBankStatement(
        cashTinkoffPluginBankStatementRequestDto $requestDto
    ): ?cashTinkoffPluginBankStatementResponseDto {
        cashTinkoffPlugin::debug('Get tinkoff bank statement');

        try {
            $requestData = ['accountNumber' => $requestDto->getAccountNumber()];

            if ($requestDto->getFrom()) {
                $requestData['from'] = $requestDto->getFrom()->format('Y-m-d');
            }

            if ($requestDto->getTill()) {
                $requestData['till'] = $requestDto->getTill()->format('Y-m-d');
            }

            cashTinkoffPlugin::debug($requestData);

            $statement = $this->waNet->query(self::BASE_URL . '/v1/bank-statement', $requestData);
        } catch (waException $exception) {
            $this->handleError($exception);

            return null;
        }

        cashTinkoffPlugin::debug(sprintf('Got response: %s', $this->waNet->getResponse(true)));

        return cashTinkoffPluginBankStatementResponseDto::fromArray($statement);
    }

    private function handleError(Exception $exception): void
    {
        $data = $this->waNet->getResponse();
        if ($data) {
            cashTinkoffPlugin::log(sprintf('Got error: %s', $exception->getMessage()));
        }

        $debug = $this->waNet->__debugInfo();
        unset($debug['options'], $debug['preview'], $debug['request_headers']);
        cashTinkoffPlugin::debug($debug);
    }
}
