<?php

class cashTinkoffPluginImporter
{
    /**
     * @var cashTinkoffPluginIntegration
     */
    private $integration;

    /**
     * @var cashTinkoffPluginTransactionSaver
     */
    private $saver;

    /**
     * @var cashTinkoffPluginCompanySettings
     */
    private $settings;

    /**
     * @var cashTransactionRepository
     */
    private $transactionRepository;

    public function __construct(
        cashTinkoffPluginIntegration $integration,
        cashTinkoffPluginCompanySettings $settings,
        cashTransactionRepository $transactionRepository
    ) {
        $this->integration = $integration;
        $this->saver = new cashTinkoffPluginTransactionSaver();
        $this->settings = $settings;
        $this->transactionRepository = $transactionRepository;
    }

    public function importAccountForDates(
        cashTinkoffPluginTinkoffAccountSettings $accountSettings,
        DateTimeImmutable $start,
        DateTimeImmutable $end
    ) {
//        $query = $this->transactionRepository->getModel()
//            ->select('*')
//            ->where(
//                'account_id = i:account_id and date between s:start and s:end',
//                [
//                    'account_id' => $this->settings->getAccount()->getId(),
//                    'start' => $start->format('Y-m-d'),
//                    'end' => $end->format('Y-m-d'),
//                ]
//            );
//        /** @var array<cashTransaction> $cashTransactions */
//        $cashTransactions = $this->transactionRepository->findByQuery($query, true);

        $request = new cashTinkoffPluginBankStatementRequestDto($accountSettings->getAccountNumber(), $start, $end);

        $response = $this->integration->getBankStatement($request);
        if (!$response) {
            return;
        }


//        $rules = (new cashTinkoffPluginMatchingRuleFactory())->createFromSetting($this->settings);

        foreach ($response->getOperation() as $operation) {
            $existing = $this->saver->findExistingTransactionByOperation($operation);
            if ($existing) {
                cashTinkoffPlugin::debug(
                    sprintf('Operation %s already exists - %s', $operation->getId(), $existing->getId())
                );

                continue;
            }

//            foreach ($cashTransactions as $i => $cashTransaction) {
//                $foundMatch = false;
//                foreach ($rules as $rule) {
//                    if (!$rule->match($cashTransaction,$operation)) {
//                        $foundMatch = false;
//
//                        break;
//                    }
//                    $foundMatch = true;
//                 }
//
//                // нашли существующую транзакцию
//                if ($foundMatch) {
//                    unset($cashTransactions[$i]);
//                }
//            }

            $newTransaction = $this->saver->saveTransaction($accountSettings, $operation);
        }
    }
}
