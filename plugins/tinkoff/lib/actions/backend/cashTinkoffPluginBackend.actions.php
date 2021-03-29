<?php

class cashTinkoffPluginBackendActions extends waViewActions
{
    public function settingsAction($params = null)
    {
        $settings = new cashTinkoffPluginSettings();

        if (empty($settings->getCompanies())) {
            $settings->addCompanySettings(cashTinkoffPluginCompanySettings::createEmpty());
        }

        $this->setLayout(new cashStaticLayout());
        $categories = cash()->getEntityRepository(cashCategory::class)->findAll();
        $this->view->assign(
            [
                'settings' => $settings->toArray(),
                'accounts' => cash()->getEntityRepository(cashAccount::class)->findAll(),
                'incomeCategories' => array_filter(
                    $categories,
                    static function (cashCategory $category) {
                        return $category->isIncome();
                    }
                ),
                'expenseCategories' => array_filter(
                    $categories,
                    static function (cashCategory $category) {
                        return $category->isExpense();
                    }
                ),
            ]
        );
    }

    public function saveTokenAction(): void
    {
        $companyName = waRequest::post('company', '', waRequest::TYPE_STRING_TRIM);
        $newToken = waRequest::post('token', '', waRequest::TYPE_STRING_TRIM);
        $settings = new cashTinkoffPluginSettings();
        $company = $settings->getCompanySettings($companyName);
        if (!$company) {
            $company = new cashTinkoffPluginCompanySettings(
                $companyName,
                false,
                new cashTinkoffPluginToken($newToken, true),
                []
            );
            $settings->addCompanySettings($company);
        } else {
            $company->setToken(new cashTinkoffPluginToken($newToken, true));
        }

        $integration = new cashTinkoffPluginIntegration($company->getToken());
        $accounts = $integration->getBankAccounts();
        if (!$accounts) {
            $this->sendJsonResponse('Check token', 'error');
        }

        foreach ($accounts as $account) {
            $company->addCompanyAccountSetting(
                cashTinkoffPluginTinkoffAccountSettings::fromTinkoffDtoResponse($account)
            );
        }

        $settings->save();

        $this->sendJsonResponse('', 'ok');
    }

    public function saveSettingsAction(): void
    {
        $data = waRequest::post('settings');
        $settings = new cashTinkoffPluginSettings();

        $settings->setEnabled((bool) ($data['enabled'] ?? false));
        if (!isset($data['companies']) || !is_array($data['companies'])) {
            $this->sendJsonResponse('Wrong data', 'fail');
        }

        foreach ($data['companies'] as $companyData) {
            $company = $settings->getCompanySettings($companyData['companyOldName']);
            if (!$company) {
                $company = new cashTinkoffPluginCompanySettings(
                    $companyData['company'],
                    $companyData['enabled'],
                    new cashTinkoffPluginToken($companyData['token'], true),
                    []
                );
                $settings->addCompanySettings($company);
            } else {
                $company->setCompany($companyData['company']);

                if (!isset($companyData['accounts']) || !is_array($companyData['accounts'])) {
                    continue;
                }

                foreach ($companyData['accounts'] as $accountDatum) {
                    $companyAccount = $company->getCompanyAccountSettings($accountDatum['number']);
                    if (!$companyAccount) {
                        continue;
                    }

                    /** @var cashAccountRepository $accountRep */
                    $accountRep = cash()->getEntityRepository(cashAccount::class);
                    /** @var cashCategoryRepository $categoryRep */
                    $categoryRep = cash()->getEntityRepository(cashCategory::class);

                    $incomeCat = $categoryRep->findById($accountDatum['income_category']);
                    if (!$incomeCat) {
                        continue;
                    }
                    $expenseCat = $categoryRep->findById($accountDatum['expense_category']);
                    if (!$expenseCat) {
                        continue;
                    }
                    $account = $accountRep->findById($accountDatum['account']);
                    if (!$account) {
                        continue;
                    }

                    $companyAccount->setEnabled((bool) ($accountDatum['enabled'] ?? false))
                        ->setExpenseCategory($expenseCat)
                        ->setIncomeCategory($incomeCat)
                        ->setAccount($account);
                }
            }
        }

        $settings->save();

        $this->sendJsonResponse('', 'ok');
    }

    public function importAction(): void
    {
        $start = DateTimeImmutable::createFromFormat('Y-m-d|', waRequest::post('start'));
        $end = DateTimeImmutable::createFromFormat('Y-m-d|', waRequest::post('end'));

        if (!$start || !$end) {
            $this->sendJsonResponse('Wrong dates', 'error');
        }

        $settings = new cashTinkoffPluginSettings();

        if (!$settings->isEnabled()) {
            $this->sendJsonResponse('Not enabled', 'error');
        }

        foreach ($settings->getCompanies() as $company) {
            $importer = new cashTinkoffPluginImporter(
                new cashTinkoffPluginIntegration($company->getToken()),
                $company,
                cash()->getEntityRepository(cashTransaction::class)
            );

            foreach ($company->getCompanyAccountSettings() as $companyAccountSetting) {
                $importer->importAccountForDates($companyAccountSetting, $start, $end);
            }
        }

        $this->sendJsonResponse('', 'ok');
    }

    protected function sendJsonResponse($response, $status): void
    {
        $this->getResponse()->addHeader('Content-Type', 'application/json');
        $response = ['status' => $status, 'data' => $response];
        $this->getResponse()->sendHeaders();

        die(json_encode($response));
    }
}
