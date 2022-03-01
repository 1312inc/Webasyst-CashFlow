<?php

class cashApiTransactionCreateRequest
{
    /**
     * @var float
     */
    private $amount = 0.0;

    /**
     * @var DateTimeImmutable
     */
    private $date = '';

    /**
     * @var int
     */
    private $accountId = 0;

    /**
     * @var int
     */
    private $categoryId = 0;

    /**
     * @var int|null
     */
    private $contractorContactId;

    /**
     * New contractor name
     *
     * @var string|null
     */
    private $contractor;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var bool|null
     */
    private $isRepeating;

    /**
     * @var int|null
     */
    private $repeatingFrequency;

    /**
     * One of 'day', 'week', 'month', 'year'.
     *
     * @see cashRepeatingTransaction
     *
     * @var string|null
     */
    private $repeatingInterval;

    /**
     * One of 'after', 'ondate'.
     *
     * @see cashRepeatingTransaction
     *
     * @var string|null
     */
    private $repeatingEndType;

    /**
     * @var int|null
     */
    private $repeatingEndAfter;

    /**
     * @var DateTimeImmutable|null
     */
    private $repeatingEndOndate;

    /**
     * @var int|null
     */
    private $transferAccountId;

    /**
     * @var string|null
     */
    private $transferIncomingAmount;

    /**
     * @var bool|null
     */
    private $isOnbadge = null;

    /**
     * @var bool|null
     */
    private $isSelfDestructWhenDue = null;

    /**
     * @var null|cashApiTransactionCreateExternalDto
     */
    private $external = null;

    public function __construct(
        float $amount,
        DateTimeImmutable $date,
        int $accountId,
        int $categoryId,
        ?int $contractorContactId,
        ?string $contractor,
        ?string $description,
        ?bool $isRepeating,
        ?int $repeatingFrequency,
        ?string $repeatingInterval,
        ?string $repeatingEndType,
        ?int $repeatingEndAfter,
        ?DateTimeImmutable $repeatingEndOndate,
        ?int $transferAccountId,
        ?string $transferIncomingAmount,
        ?bool $isOnbadge,
        ?cashApiTransactionCreateExternalDto $external,
        ?bool $isSelfDestructWhenDue
    ) {
        $this->amount = $amount;
        $this->date = $date;
        $this->accountId = $accountId;
        $this->categoryId = $categoryId;
        $this->contractorContactId = $contractorContactId;
        $this->contractor = $contractor;
        $this->description = $description;
        $this->isRepeating = $isRepeating;
        $this->repeatingFrequency = $repeatingFrequency;
        $this->repeatingInterval = $repeatingInterval;
        $this->repeatingEndType = $repeatingEndType;
        $this->repeatingEndAfter = $repeatingEndAfter;
        $this->repeatingEndOndate = $repeatingEndOndate;
        $this->transferAccountId = $transferAccountId;
        $this->transferIncomingAmount = $transferIncomingAmount;
        $this->isOnbadge = $isOnbadge;
        $this->external = $external;
        $this->isSelfDestructWhenDue = $isSelfDestructWhenDue;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getAccountId(): int
    {
        return $this->accountId;
    }

    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    public function getContractorContactId(): ?int
    {
        return $this->contractorContactId;
    }

    public function setContractorContactId(?int $contractorContactId): cashApiTransactionCreateRequest
    {
        $this->contractorContactId = $contractorContactId;

        return $this;
    }

    public function getContractor(): ?string
    {
        return $this->contractor;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getIsRepeating(): ?bool
    {
        return $this->isRepeating;
    }

    public function getRepeatingFrequency(): ?int
    {
        return $this->repeatingFrequency;
    }

    public function getRepeatingInterval(): ?string
    {
        return $this->repeatingInterval;
    }

    public function getRepeatingEndType(): ?string
    {
        return $this->repeatingEndType;
    }

    public function getRepeatingEndAfter(): ?int
    {
        return $this->repeatingEndAfter;
    }

    public function getRepeatingEndOndate(): ?DateTimeImmutable
    {
        return $this->repeatingEndOndate;
    }

    public function getTransferAccountId(): ?int
    {
        return $this->transferAccountId;
    }

    public function getTransferIncomingAmount(): ?string
    {
        return $this->transferIncomingAmount;
    }

    public function isOnbadge(): ?bool
    {
        return $this->isOnbadge;
    }

    public function getExternal(): ?cashApiTransactionCreateExternalDto
    {
        return $this->external;
    }

    public function isSelfDestructWhenDue(): ?bool
    {
        return $this->isSelfDestructWhenDue;
    }
}
