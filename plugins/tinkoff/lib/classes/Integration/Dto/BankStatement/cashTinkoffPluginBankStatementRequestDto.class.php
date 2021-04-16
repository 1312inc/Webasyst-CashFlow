<?php

final class cashTinkoffPluginBankStatementRequestDto
{
    /**
     * @var string
     */
    private $accountNumber;

    /**
     * @var DateTimeImmutable|null
     */
    private $from;

    /**
     * @var DateTimeImmutable|null
     */
    private $till;

    public function __construct(string $accountNumber, ?DateTimeImmutable $from, ?DateTimeImmutable $till)
    {
        if (empty($accountNumber)) {
            throw new cashTinkoffPluginException('Empty account number');
        }

        if ($from && $till && $from > $till) {
            throw new cashTinkoffPluginException('From can not be greater then till');
        }

        $this->accountNumber = $accountNumber;
        $this->from = $from;
        $this->till = $till;
    }

    public function getAccountNumber(): string
    {
        return $this->accountNumber;
    }

    public function getFrom(): ?DateTimeImmutable
    {
        return $this->from;
    }

    public function getTill(): ?DateTimeImmutable
    {
        return $this->till;
    }
}
