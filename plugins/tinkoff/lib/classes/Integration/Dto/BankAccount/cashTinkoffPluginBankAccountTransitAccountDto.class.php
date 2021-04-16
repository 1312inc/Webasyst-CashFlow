<?php

final class cashTinkoffPluginBankAccountTransitAccountDto
{
    /**
     * Номер транзитного банковского счета
     *
     * @var string
     */
    private $accountNumber;

    /**
     * Текущий остаток по транзитному счету
     *
     * @var float
     */
    private $balance;

    public function __construct(string $accountNumber, float $balance)
    {
        $this->accountNumber = $accountNumber;
        $this->balance = $balance;
    }

    public static function fromArray(array $data): self
    {
        return new self($data['accountNumber'], (float) $data['balance']);
    }

    public function getAccountNumber(): string
    {
        return $this->accountNumber;
    }

    public function getBalance(): float
    {
        return $this->balance;
    }
}
