<?php

final class cashApiContactGetListDto
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $lastTransactionDate;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $firstname;

    /**
     * @var string
     */
    private $lastname;

    /**
     * @var string
     */
    private $photoUrl;

    /**
     * @var string
     */
    private $photoUrlAbsolute;

    /**
     * @var array
     */
    private $stat;

    /**
     * @var float
     */
    private $lastTransactionAmount;

    /**
     * @var string
     */
    private $lastTransactionCurrency;

    public function __construct(
        int $id,
        string $lastTransactionDate,
        float $lastTransactionAmount,
        string $lastTransactionCurrency,
        string $name,
        string $firstname,
        string $lastname,
        string $photoUrl,
        string $photoUrlAbsolute,
        array $stat
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->photoUrl = $photoUrl;
        $this->photoUrlAbsolute = $photoUrlAbsolute;
        $this->stat = $stat;
        $this->lastTransactionDate = $lastTransactionDate;
        $this->lastTransactionAmount = $lastTransactionAmount;
        $this->lastTransactionCurrency = $lastTransactionCurrency;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function getPhotoUrl(): string
    {
        return $this->photoUrl;
    }

    public function getPhotoUrlAbsolute(): string
    {
        return $this->photoUrlAbsolute;
    }

    public function getStat(): array
    {
        return $this->stat;
    }

    public function getLastTransactionDate(): string
    {
        return $this->lastTransactionDate;
    }

    public function getLastTransactionAmount(): float
    {
        return $this->lastTransactionAmount;
    }

    public function getLastTransactionCurrency(): string
    {
        return $this->lastTransactionCurrency;
    }
}
