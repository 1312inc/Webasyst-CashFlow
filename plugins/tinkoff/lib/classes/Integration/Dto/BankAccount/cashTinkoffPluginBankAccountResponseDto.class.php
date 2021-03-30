<?php

final class cashTinkoffPluginBankAccountResponseDto implements cashTinkoffPluginToArrayInterface
{
    use cashTinkoffPluginToArrayTrait;

    /**
     * Расчетный счет организации
     *
     * @var string
     */
    private $accountNumber;

    /**
     * Наименование счета
     *
     * @var string
     */
    private $name;

    /**
     * Код валюты счета по ОКВ (цифрами)
     *
     * @var string
     */
    private $currency;

    /**
     * БИК банка
     *
     * @var string
     */
    private $bankBik;

    /**
     * Тип счета. Может принимать одно из следующих значений(список вариантов значений может пополняться):
     *
     * Current - расчетный счет
     * Tax - счет Тинькофф Бухгалтерии
     * Overnight - счет Overnight
     * Cashbox - бизнес-копилка
     * Tender - специальный счет для участия в госзакупках
     * Trust - специальный счет доверительного управляющего ПИФ
     * Broker - специальный брокерский счет
     * BankPaymentAgent - специальный счет банковского платежного агента
     * PaymentAgent - счет платежного агента
     * Nominal - номинальный счет
     * NominalIpo - номинальный счет
     * TrustManagementSmp - специальный счет доверительного управления
     *
     * @var string
     */
    private $accountType;

    /**
     * Дата активации
     *
     * @var DateTimeImmutable|null
     */
    private $activationDate;

    /**
     * Баланс счета
     *
     * @var cashTinkoffPluginBankAccountBalanceDto
     */
    private $balance;

    /**
     * Информация о транзитном счете. Актуально для валютных счетов
     *
     * @var ?cashTinkoffPluginBankAccountTransitAccountDto
     */
    private $transitAccount;

    public function __construct(
        string $accountNumber,
        string $name,
        string $currency,
        string $bankBik,
        string $accountType,
        ?DateTimeImmutable $activationDate,
        cashTinkoffPluginBankAccountBalanceDto $balance,
        ?cashTinkoffPluginBankAccountTransitAccountDto $transitAccount
    ) {
        $this->accountNumber = $accountNumber;
        $this->name = $name;
        $this->currency = $currency;
        $this->bankBik = $bankBik;
        $this->accountType = $accountType;
        $this->activationDate = $activationDate;
        $this->balance = $balance;
        $this->transitAccount = $transitAccount;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['accountNumber'],
            $data['name'],
            $data['currency'],
            $data['bankBik'],
            $data['accountType'],
            isset($data['activationDate'])
                ? DateTimeImmutable::createFromFormat('Y-m-d', $data['activationDate'])
                : null,
            cashTinkoffPluginBankAccountBalanceDto::fromArray($data['balance']),
            isset($data['transitAccount'])
                ? cashTinkoffPluginBankAccountTransitAccountDto::fromArray($data['transitAccount'])
                : null
        );
    }

    public function getAccountNumber(): string
    {
        return $this->accountNumber;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getBankBik(): string
    {
        return $this->bankBik;
    }

    public function getAccountType(): string
    {
        return $this->accountType;
    }

    public function getActivationDate(): ?DateTimeImmutable
    {
        return $this->activationDate;
    }

    public function getBalance(): cashTinkoffPluginBankAccountBalanceDto
    {
        return $this->balance;
    }

    public function getTransitAccount(): ?cashTinkoffPluginBankAccountTransitAccountDto
    {
        return $this->transitAccount;
    }
}
