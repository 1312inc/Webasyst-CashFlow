<?php

final class cashTinkoffPluginTinkoffAccountSettings implements cashTinkoffPluginEnablableInterface, JsonSerializable, cashTinkoffPluginToArrayInterface
{
    /**
     * @var string
     */
    private $accountNumber;

    /**
     * @var bool
     */
    private $enabled;

    /**
     * @var cashCategory
     */
    private $incomeCategory;

    /**
     * @var cashCategory
     */
    private $expenseCategory;

    /**
     * @var cashAccount
     */
    private $account;

    /**
     * @var array
     */
    private $tinkoffData = [];

    public function __construct(
        string $accountNumber,
        bool $enabled,
        cashAccount $account,
        cashCategory $incomeCategory,
        cashCategory $expenseCategory,
        array $tinkoffData = []
    ) {
        $this->enabled = $enabled;
        $this->accountNumber = $accountNumber;
        $this->account = $account;
        $this->incomeCategory = $incomeCategory;
        $this->expenseCategory = $expenseCategory;
        $this->tinkoffData = $tinkoffData;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getIncomeCategory(): cashCategory
    {
        return $this->incomeCategory;
    }

    public function getExpenseCategory(): cashCategory
    {
        return $this->expenseCategory;
    }

    public function getAccount(): cashAccount
    {
        return $this->account;
    }

    public function setAccount(cashAccount $account): self
    {
        $this->account = $account;

        return $this;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function setIncomeCategory(cashCategory $incomeCategory): self
    {
        $this->incomeCategory = $incomeCategory;

        return $this;
    }

    public function setExpenseCategory(cashCategory $expenseCategory): self
    {
        $this->expenseCategory = $expenseCategory;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccountNumber(): string
    {
        return $this->accountNumber;
    }

    public function setAccountNumber(string $accountNumber): self
    {
        $this->accountNumber = $accountNumber;

        return $this;
    }

    public static function fromArray(array $data): self
    {
        if (empty($data['number'])) {
            throw new InvalidArgumentException('No number for cashTinkoffPluginCompanyAccountSettings');
        }

        $account = isset($data['account'])
            ? cash()->getEntityRepository(cashAccount::class)->findById((int) $data['account'])
            : null;
        if (!$account) {
            throw new InvalidArgumentException('No account for cashTinkoffPluginCompanyAccountSettings');
        }

        $categoryRepo = cash()->getEntityRepository(cashCategory::class);

        $incomeCategory = !empty($data['income_category'])
            ? $categoryRepo->findById((int) $data['income_category'])
            : $categoryRepo->findNoCategoryIncome();
        if (!$incomeCategory) {
            throw new InvalidArgumentException('No income_category for cashTinkoffPluginCompanyAccountSettings');
        }

        $expenseCategory = !empty($data['expense_category'])
            ? $categoryRepo->findById((int) $data['expense_category'])
            : $categoryRepo->findNoCategoryExpense();
        if (!$expenseCategory) {
            throw new InvalidArgumentException('No expense_category for cashTinkoffPluginCompanyAccountSettings');
        }

        return new self(
            (string) $data['number'],
            (bool) ($data['enabled'] ?? false),
            $account,
            $incomeCategory,
            $expenseCategory
        );
    }

    public function getTinkoffData(): array
    {
        return $this->tinkoffData;
    }

    public function setTinkoffData(array $tinkoffData): self
    {
        $this->tinkoffData = $tinkoffData;

        return $this;
    }

    public function __toString(): string
    {
        return $this->accountNumber;
    }

    public static function fromTinkoffDtoResponse(cashTinkoffPluginBankAccountResponseDto $responseDto): self
    {
        $settings = self::createEmpty();
        $settings
            ->setAccountNumber($responseDto->getAccountNumber())
            ->setTinkoffData($responseDto->toArray());

        return $settings;
    }

    public static function createEmpty(): self
    {
        /** @var cashAccountRepository $accountRep */
        $accountRep = cash()->getEntityRepository(cashAccount::class);
        /** @var cashCategoryRepository $categoryRep */
        $categoryRep = cash()->getEntityRepository(cashCategory::class);

        return new self(
            '',
            false,
            $accountRep->findFirstForContact(),
            $categoryRep->findNoCategoryIncome(),
            $categoryRep->findNoCategoryExpense(),
            []
        );
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toArray(): array
    {
        return [
            'enabled' => $this->enabled,
            'number' => $this->accountNumber,
            'income_category' => $this->incomeCategory->getId(),
            'expense_category' => $this->expenseCategory->getId(),
            'account' => $this->account->getId(),
            'tinkoff_data' => $this->tinkoffData,
        ];
    }
}
