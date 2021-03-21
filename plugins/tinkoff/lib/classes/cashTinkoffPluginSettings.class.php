<?php

final class cashTinkoffPluginSettings
{
    /**
     * @var bool
     */
    private $enabled;

    /**
     * @var ?cashTinkoffPluginToken
     */
    private $token;

    /**
     * @var ?cashCategory
     */
    private $incomeCategory;

    /**
     * @var ?cashCategory
     */
    private $expenseCategory;

    /**
     * @var ?cashAccount
     */
    private $account;

    /**
     * @var waAppSettingsModel
     */
    private $settingsModel;

    public function __construct()
    {
        $this->settingsModel = new waAppSettingsModel();
        $rawSettings = json_decode($this->settingsModel->get($this->getSettingsKey(), 'settings'), true);

        $this->enabled = !empty($rawSettings['enabled']);

        if (!empty($rawSettings['account'])) {
            $this->account = cash()->getEntityRepository(cashAccount::class)->findById((int) $rawSettings['account']);
        }

        $categoryRepo = cash()->getEntityRepository(cashCategory::class);
        if (!empty($rawSettings['income_category'])) {
            $this->incomeCategory = $categoryRepo->findById((int) $rawSettings['income_category']);
        } else {
            $this->incomeCategory = $categoryRepo->findNoCategoryIncome();
        }

        if (!empty($rawSettings['expense_category'])) {
            $this->expenseCategory = $categoryRepo->findById((int) $rawSettings['expense_category']);
        } else {
            $this->expenseCategory = $categoryRepo->findNoCategoryExpense();
        }

        if (!empty($rawSettings['token'])) {
            $this->token = new cashTinkoffPluginToken($rawSettings['token']);
        }

        if (!$this->account || !$this->token) {
            $this->enabled = false;
        }
    }

    public function save(): bool
    {
        $rawSettings = [
            'enabled' => $this->enabled,
            'token' => $this->token ? $this->token->getValue() : null,
            'income_category' => $this->incomeCategory ? $this->incomeCategory->getId() : null,
            'expense_category' => $this->expenseCategory ? $this->expenseCategory->getId() : null,
            'account' => $this->account ? $this->account->getId() : null,
        ];

        try {
            $this->settingsModel->set($this->getSettingsKey(), 'settings', json_encode($rawSettings));

            return true;
        } catch (waException $e) {
            cashTinkoffPlugin::log($e);
        }

        return false;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getToken(): ?cashTinkoffPluginToken
    {
        return $this->token;
    }

    public function getIncomeCategory()
    {
        return $this->incomeCategory;
    }

    public function getExpenseCategory()
    {
        return $this->expenseCategory;
    }

    public function getAccount()
    {
        return $this->account;
    }

    public function getPlugin(): cashTinkoffPlugin
    {
        return $this->plugin;
    }

    public function setEnabled(bool $enabled): cashTinkoffPluginSettings
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function setToken(?cashTinkoffPluginToken $token): cashTinkoffPluginSettings
    {
        $this->token = $token;

        return $this;
    }

    public function setIncomeCategory($incomeCategory)
    {
        $this->incomeCategory = $incomeCategory;

        return $this;
    }

    public function setExpenseCategory($expenseCategory)
    {
        $this->expenseCategory = $expenseCategory;

        return $this;
    }

    public function setAccount($account)
    {
        $this->account = $account;

        return $this;
    }

    private function getSettingsKey(): array
    {
        return ['cash', 'tinkoff'];
    }
}
