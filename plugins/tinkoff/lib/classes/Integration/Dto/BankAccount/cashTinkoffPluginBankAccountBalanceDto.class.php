<?php

final class cashTinkoffPluginBankAccountBalanceDto
{
    /**
     * Доступный остаток
     *
     * @var float
     */
    private $otb;

    /**
     * Сумма авторизаций (захолдированные на счете средства)
     *
     * @var float
     */
    private $authorized;

    /**
     * Сумма платежей в картотеке клиента (собственные платежи)
     *
     * @var float
     */
    private $pendingPayments;

    /**
     * Сумма платежей в картотеке банка (требования к клиенту)
     *
     * @var float
     */
    private $pendingRequisitions;

    public function __construct(float $otb, float $authorized, float $pendingPayments, float $pendingRequisitions)
    {
        $this->otb = $otb;
        $this->authorized = $authorized;
        $this->pendingPayments = $pendingPayments;
        $this->pendingRequisitions = $pendingRequisitions;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (float) ($data['otb'] ?? 0),
            (float) ($data['authorized'] ?? 0),
            (float) ($data['pendingPayments'] ?? 0),
            (float) ($data['pendingRequisitions'] ?? 0)
        );
    }

    public function getOtb(): float
    {
        return $this->otb;
    }

    public function getAuthorized(): float
    {
        return $this->authorized;
    }

    public function getPendingPayments(): float
    {
        return $this->pendingPayments;
    }

    public function getPendingRequisitions(): string
    {
        return $this->pendingRequisitions;
    }
}
