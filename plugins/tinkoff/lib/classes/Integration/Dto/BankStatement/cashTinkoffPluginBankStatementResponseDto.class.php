<?php

final class cashTinkoffPluginBankStatementResponseDto
{
    /**
     * Расчетный счет организации
     *
     * @var string
     */
    private $accountNumber;

    /**
     * Баланс на начало периода
     *
     * @var float
     */
    private $saldoIn;

    /**
     * Обороты входящих платежей
     *
     * @var float
     */
    private $income;

    /**
     * Обороты исходящих платежей
     *
     * @var float
     */
    private $outcome;

    /**
     * Баланс на конец периода
     *
     * @var float
     */
    private $saldoOut;

    /**
     * Список операций по счету
     *
     * @var array<cashTinkoffPluginBankStatementOperationDto>
     */
    private $operation;

    /**
     * @param cashTinkoffPluginBankStatementOperationDto[] $operation
     */
    public function __construct(
        string $accountNumber,
        float $saldoIn,
        float $income,
        float $outcome,
        float $saldoOut,
        array $operation
    ) {
        $this->accountNumber = $accountNumber;
        $this->saldoIn = $saldoIn;
        $this->income = $income;
        $this->outcome = $outcome;
        $this->saldoOut = $saldoOut;
        $this->operation = $operation;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['accountNumber'],
            (float) $data['saldoIn'],
            (float) $data['income'],
            (float) $data['outcome'],
            (float) $data['saldoOut'],
            cashTinkoffPluginBankStatementOperationDto::collectionFromArray($data['operation'] ?? [])
        );
    }

    public function getAccountNumber(): string
    {
        return $this->accountNumber;
    }

    public function getSaldoIn(): float
    {
        return $this->saldoIn;
    }

    public function getIncome(): float
    {
        return $this->income;
    }

    public function getOutcome(): float
    {
        return $this->outcome;
    }

    public function getSaldoOut(): float
    {
        return $this->saldoOut;
    }

    public function getOperation(): array
    {
        return $this->operation;
    }
}
